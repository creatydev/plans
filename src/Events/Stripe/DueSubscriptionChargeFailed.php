<?php

namespace Rennokki\Plans\Events\Stripe;

use Illuminate\Queue\SerializesModels;

class DueSubscriptionChargeFailed
{
    use SerializesModels;

    public $model;
    public $subscription;
    public $exception;

    /**
     * @param Model $model The model on which the action was done.
     * @param SubscriptionModel $subscription Subscription due.
     * @param Exception The exception thrown by the Stripe\Charge::create() call.
     * @return void
     */
    public function __construct($model, $subscription, $exception)
    {
        $this->model = $model;
        $this->subscription = $subscription;
        $this->exception = $exception;
    }
}
