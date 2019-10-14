<?php

namespace Rennokki\Plans\Events;

use Illuminate\Queue\SerializesModels;

class NewSubscription
{
    use SerializesModels;

    public $model;
    public $subscription;

    /**
     * @param Model $model The model that subscribed.
     * @param SubscriptionModel $subscription Subscription the model has subscribed to.
     * @return void
     */
    public function __construct($model, $subscription)
    {
        $this->model = $model;
        $this->subscription = $subscription;
    }
}
