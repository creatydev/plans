<?php

namespace Rennokki\Plans\Events\Stripe;

use Stripe\Charge as StripeCharge;
use Illuminate\Queue\SerializesModels;

class DueSubscriptionChargeSuccess
{
    use SerializesModels;

    public $model;
    public $subscription;
    public $stripeCharge;

    /**
     * @param Model $model The model on which the action was done.
     * @param SubscriptionModel $subscription Due subscription that was paid.
     * @param Stripe\Charge The result of the Stripe\Charge::create() call.
     * @return void
     */
    public function __construct($model, $subscription, StripeCharge $stripeCharge)
    {
        $this->model = $model;
        $this->subscription = $subscription;
        $this->stripeCharge = $stripeCharge;
    }
}
