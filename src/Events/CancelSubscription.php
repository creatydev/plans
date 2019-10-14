<?php

namespace Rennokki\Plans\Events;

use Illuminate\Queue\SerializesModels;

class CancelSubscription
{
    use SerializesModels;

    public $model;
    public $subscription;

    /**
     * @param Model $model The model on which the action was done.
     * @param SubscriptionModel $subscription Subscription that was cancelled.
     * @return void
     */
    public function __construct($model, $subscription)
    {
        $this->model = $model;
        $this->subscription = $subscription;
    }
}
