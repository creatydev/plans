<?php

namespace Rennokki\Plans\Events;

use Illuminate\Queue\SerializesModels;

class UpgradeSubscription
{
    use SerializesModels;

    public $model;
    public $subscription;
    public $startFromNow;
    public $oldPlan;
    public $newPlan;

    /**
     * @param Model $model The model on which the action was done.
     * @param SubscriptionModel $subscription Subscription that was upgraded.
     * @param bool $startFromNow Wether the current subscription is upgraded by extending now or is upgraded at the next cycle.
     * @param null|PlanModel $oldPlan The old plan.
     * @param null|PlanModel $newPlan The new plan.
     * @return void
     */
    public function __construct($model, $subscription, bool $startFromNow, $oldPlan, $newPlan)
    {
        $this->model = $model;
        $this->subscription = $subscription;
        $this->startFromNow = $startFromNow;
        $this->oldPlan = $oldPlan;
        $this->newPlan = $newPlan;
    }
}
