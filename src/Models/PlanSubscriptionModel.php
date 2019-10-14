<?php

namespace Rennokki\Plans\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Rennokki\Plans\Exceptions\UnsupportedPaymentMethodException;

class PlanSubscriptionModel extends Model
{
    protected $table = 'plan_subscriptions';
    protected $guarded = [];
    protected $fillable = ['plan_id', 'model_id', 'model_type', 'payment_method', 'is_paid', 'charging_price', 'charging_currency', 'is_recurring', 'recurring_each_days', 'starts_on', 'expires_on', 'cancelled_on'];
    protected $dates = [
        'starts_on',
        'expires_on',
        'cancelled_on',
    ];
    protected $casts = [
        'is_paid' => 'boolean',
        'is_recurring' => 'boolean',
    ];

    public function model()
    {
        return $this->morphTo();
    }

    public function plan()
    {
        return $this->belongsTo(config('plans.models.plan'), 'plan_id');
    }

    public function features()
    {
        return $this->plan()->first()->features();
    }

    public function usages()
    {
        return $this->hasMany(config('plans.models.usage'), 'subscription_id');
    }

    public function scopePaid($query)
    {
        return $query->where('is_paid', true);
    }

    public function scopeUnpaid($query)
    {
        return $query->where('is_paid', false);
    }

    public function scopeExpired($query)
    {
        return $query->where('expires_on', '<', Carbon::now()->toDateTimeString());
    }

    public function scopeRecurring($query)
    {
        return $query->where('is_recurring', true);
    }

    public function scopeCancelled($query)
    {
        return $query->whereNotNull('cancelled_on');
    }

    public function scopeNotCancelled($query)
    {
        return $query->whereNull('cancelled_on');
    }

    public function scopeStripe($query)
    {
        return $query->where('payment_method', 'stripe');
    }

    /**
     * Checks if the current subscription has started.
     *
     * @return bool
     */
    public function hasStarted()
    {
        return (bool) Carbon::now()->greaterThanOrEqualTo(Carbon::parse($this->starts_on));
    }

    /**
     * Checks if the current subscription has expired.
     *
     * @return bool
     */
    public function hasExpired()
    {
        return (bool) Carbon::now()->greaterThan(Carbon::parse($this->expires_on));
    }

    /**
     * Checks if the current subscription is active.
     *
     * @return bool
     */
    public function isActive()
    {
        return (bool) ($this->hasStarted() && ! $this->hasExpired());
    }

    /**
     * Get the remaining days in this subscription.
     *
     * @return int
     */
    public function remainingDays()
    {
        if ($this->hasExpired()) {
            return (int) 0;
        }

        return (int) Carbon::now()->diffInDays(Carbon::parse($this->expires_on));
    }

    /**
     * Checks if the current subscription is cancelled (expiration date is in the past & the subscription is cancelled).
     *
     * @return bool
     */
    public function isCancelled()
    {
        return (bool) $this->cancelled_on != null;
    }

    /**
     * Checks if the current subscription is pending cancellation.
     *
     * @return bool
     */
    public function isPendingCancellation()
    {
        return (bool) ($this->isCancelled() && $this->isActive());
    }

    /**
     * Cancel this subscription.
     *
     * @return self $this
     */
    public function cancel()
    {
        $this->update([
            'cancelled_on' => Carbon::now(),
        ]);

        return $this;
    }

    /**
     * Consume a feature, if it is 'limit' type.
     *
     * @param string $featureCode The feature code. This feature has to be 'limit' type.
     * @param float $amount The amount consumed.
     * @return bool Wether the feature was consumed successfully or not.
     */
    public function consumeFeature(string $featureCode, float $amount)
    {
        $usageModel = config('plans.models.usage');

        $feature = $this->features()->code($featureCode)->first();

        if (! $feature || $feature->type != 'limit') {
            return false;
        }

        $usage = $this->usages()->code($featureCode)->first();

        if (! $usage) {
            $usage = $this->usages()->save(new $usageModel([
                'code' => $featureCode,
                'used' => 0,
            ]));
        }

        if (! $feature->isUnlimited() && $usage->used + $amount > $feature->limit) {
            return false;
        }

        $remaining = (float) ($feature->isUnlimited()) ? -1 : $feature->limit - ($usage->used + $amount);

        event(new \Rennokki\Plans\Events\FeatureConsumed($this, $feature, $amount, $remaining));

        return $usage->update([
            'used' => (float) ($usage->used + $amount),
        ]);
    }

    /**
     * Reverse of the consume a feature method, if it is 'limit' type.
     *
     * @param string $featureCode The feature code. This feature has to be 'limit' type.
     * @param float $amount The amount consumed.
     * @return bool Wether the feature was consumed successfully or not.
     */
    public function unconsumeFeature(string $featureCode, float $amount)
    {
        $usageModel = config('plans.models.usage');

        $feature = $this->features()->code($featureCode)->first();

        if (! $feature || $feature->type != 'limit') {
            return false;
        }

        $usage = $this->usages()->code($featureCode)->first();

        if (! $usage) {
            $usage = $this->usages()->save(new $usageModel([
                'code' => $featureCode,
                'used' => 0,
            ]));
        }

        $used = (float) ($feature->isUnlimited()) ? ($usage->used - $amount < 0) ? 0 : ($usage->used - $amount) : ($usage->used - $amount);
        $remaining = (float) ($feature->isUnlimited()) ? -1 : ($used > 0) ? ($feature->limit - $used) : $feature->limit;

        event(new \Rennokki\Plans\Events\FeatureUnconsumed($this, $feature, $amount, $remaining));

        return $usage->update([
            'used' => $used,
        ]);
    }

    /**
     * Get the amount used for a limit.
     *
     * @param string $featureCode The feature code. This feature has to be 'limit' type.
     * @return null|float Null if doesn't exist, integer with the usage.
     */
    public function getUsageOf(string $featureCode)
    {
        $usage = $this->usages()->code($featureCode)->first();
        $feature = $this->features()->code($featureCode)->first();

        if (! $feature || $feature->type != 'limit') {
            return;
        }

        if (! $usage) {
            return 0;
        }

        return (float) $usage->used;
    }

    /**
     * Get the amount remaining for a feature.
     *
     * @param string $featureCode The feature code. This feature has to be 'limit' type.
     * @return float The amount remaining.
     */
    public function getRemainingOf(string $featureCode)
    {
        $usage = $this->usages()->code($featureCode)->first();
        $feature = $this->features()->code($featureCode)->first();

        if (! $feature || $feature->type != 'limit') {
            return 0;
        }

        if (! $usage) {
            return (float) ($feature->isUnlimited()) ? -1 : $feature->limit;
        }

        return (float) ($feature->isUnlimited()) ? -1 : ($feature->limit - $usage->used);
    }

    /**
     * @param $value Payment Method
     * Supported payment methods in the plans.payment_methods array
     * If we need add a new payment method, just append the array and use all system.
     */
    public function setPaymentMethodAttribute($value)
    {
        if (! is_null($value)) {
            $supportedPaymentMethods = config('plans.payment_methods', ['stripe']);
            if (! in_array($value, $supportedPaymentMethods)) {
                throw new UnsupportedPaymentMethodException(sprintf('The payment method (%s) does not supported. Supported payment methods: %s', $value, implode(', ', $supportedPaymentMethods)));
            }
        } else {
            $this->attributes['payment_method'] = $value;
        }
    }
}
