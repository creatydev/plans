<?php

namespace Rennokki\Plans\Events;

use Illuminate\Queue\SerializesModels;

class ExtendSubscription
{
    use SerializesModels;

    public $model;
    public $subscription;
    public $startFromNow;
    public $newSubscription;

    /**
     * @param Model $model The model on which the action was done.
     * @param SubscriptionModel $subscription Subscription that was extended.
     * @param bool $startFromNow Wether the current subscription is extended or is created at the next cycle.
     * @param null|SubscriptionModel $newSubscription Null if $startFromNow is true; The new subscription created in extension.
     * @return void
     */
    public function __construct($model, $subscription, bool $startFromNow, $newSubscription)
    {
        $this->model = $model;
        $this->subscription = $subscription;
        $this->startFromNow = $startFromNow;
        $this->newSubscription = $newSubscription;
    }
}
