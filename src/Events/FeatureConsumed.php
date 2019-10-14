<?php

namespace Rennokki\Plans\Events;

use Illuminate\Queue\SerializesModels;

class FeatureConsumed
{
    use SerializesModels;

    public $subscription;
    public $feature;
    public $used;
    public $remaining;

    /**
     * @param SubscriptionModel $subscription Subscription on which action was done.
     * @param FeatureModel $feature The feature that was consumed.
     * @param float $used The amount used on this consumption.
     * @param float $remaining The amount remaining for this feature.
     * @return void
     */
    public function __construct($subscription, $feature, float $used, float $remaining)
    {
        $this->subscription = $subscription;
        $this->feature = $feature;
        $this->used = $used;
        $this->remaining = $remaining;
    }
}
