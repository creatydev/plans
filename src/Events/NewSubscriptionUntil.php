<?php

namespace Rennokki\Plans\Events;

use Illuminate\Queue\SerializesModels;

class NewSubscriptionUntil
{
    use SerializesModels;

    public $model;
    public $subscription;
    public $expiresOn;

    /**
     * @param Model $model The model that subscribed.
     * @param SubscriptionModel $subscription Subscription the model has subscribed to.
     * @param Carbon $expiresOn The date when the subscription expires.
     * @return void
     */
    public function __construct($model, $subscription, $expiresOn)
    {
        $this->model = $model;
        $this->subscription = $subscription;
        $this->expiresOn = $expiresOn;
    }
}
