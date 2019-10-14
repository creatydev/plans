<?php

namespace Rennokki\Plans\Models;

use Illuminate\Database\Eloquent\Model;

class PlanSubscriptionUsageModel extends Model
{
    protected $table = 'plan_subscription_usages';
    protected $guarded = [];
    protected $fillable = ['subscription_id', 'code', 'used'];

    public function subscription()
    {
        return $this->belongsTo(config('plans.models.subscription'), 'subscription_id');
    }

    public function scopeCode($query, string $code)
    {
        return $query->where('code', $code);
    }
}
