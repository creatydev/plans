<?php

namespace Rennokki\Plans\Test;

use Carbon\Carbon;

class PlanTest extends TestCase
{
    protected $user;
    protected $plan;
    protected $newPlan;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = factory(\Rennokki\Plans\Test\Models\User::class)->create();
        $this->plan = factory(\Rennokki\Plans\Models\PlanModel::class)->create();
        $this->newPlan = factory(\Rennokki\Plans\Models\PlanModel::class)->create();
    }

    /**
     * @group basic
     */
    public function testNoSubscriptions()
    {
        $this->assertNull($this->user->subscriptions()->first());
        $this->assertNull($this->user->activeSubscription());
        $this->assertNull($this->user->lastActiveSubscription());
        $this->assertFalse($this->user->hasActiveSubscription());
    }

    /**
     * @group basic
     */
    public function testSubscribeToWithInvalidDuration()
    {
        $this->assertFalse($this->user->subscribeTo($this->plan, 0));
        $this->assertFalse($this->user->subscribeTo($this->plan, -1));
    }

    /**
     * @group basic
     */
    public function testSubscribeToWithInvalidDate()
    {
        $this->assertFalse($this->user->subscribeToUntil($this->plan, Carbon::yesterday()));
        $this->assertFalse($this->user->subscribeToUntil($this->plan, Carbon::yesterday()->toDateTimeString()));
        $this->assertFalse($this->user->subscribeToUntil($this->plan, Carbon::yesterday()->toDateString()));
    }

    /**
     * @group basic
     */
    public function testSubscribeTo()
    {
        $subscription = $this->user->subscribeTo($this->plan, 15);
        sleep(1);

        $this->assertEquals($subscription->plan_id, $this->plan->id);
        $this->assertNotNull($this->user->subscriptions()->first());
        $this->assertEquals($this->user->subscriptions()->expired()->count(), 0);
        $this->assertEquals($this->user->subscriptions()->recurring()->count(), 1);
        $this->assertEquals($this->user->subscriptions()->cancelled()->count(), 0);
        $this->assertNotNull($this->user->activeSubscription());

        $this->assertNotNull($this->user->lastActiveSubscription());
        $this->assertTrue($this->user->hasActiveSubscription());
        $this->assertEquals($subscription->remainingDays(), 14);
    }

    /**
     * @group basic
     */
    public function testSubscribeToUntilWithCarboninstance()
    {
        $subscription = $this->user->subscribeToUntil($this->plan, Carbon::now()->addDays(15));
        sleep(1);

        $this->assertEquals($subscription->plan_id, $this->plan->id);
        $this->assertNotNull($this->user->subscriptions()->first());
        $this->assertEquals($this->user->subscriptions()->expired()->count(), 0);
        $this->assertEquals($this->user->subscriptions()->recurring()->count(), 1);
        $this->assertEquals($this->user->subscriptions()->cancelled()->count(), 0);
        $this->assertNotNull($this->user->activeSubscription());
        $this->assertNotNull($this->user->lastActiveSubscription());
        $this->assertTrue($this->user->hasActiveSubscription());
        $this->assertEquals($subscription->remainingDays(), 14);
    }

    /**
     * @group basic
     */
    public function testSubscribeToUntilWithDateTimeString()
    {
        $subscription = $this->user->subscribeToUntil($this->plan, Carbon::now()->addDays(15)->toDateTimeString());
        sleep(1);

        $this->assertEquals($subscription->plan_id, $this->plan->id);
        $this->assertNotNull($this->user->subscriptions()->first());
        $this->assertNotNull($this->user->activeSubscription());
        $this->assertNotNull($this->user->lastActiveSubscription());
        $this->assertTrue($this->user->hasActiveSubscription());
        $this->assertEquals($subscription->remainingDays(), 14);
    }

    /**
     * @group basic
     */
    public function testSubscribeToUntilWithDateString()
    {
        $subscription = $this->user->subscribeToUntil($this->plan, Carbon::now()->addDays(15)->toDateString());
        sleep(1);

        $this->assertEquals($subscription->plan_id, $this->plan->id);
        $this->assertNotNull($this->user->subscriptions()->first());
        $this->assertNotNull($this->user->activeSubscription());
        $this->assertNotNull($this->user->lastActiveSubscription());
        $this->assertTrue($this->user->hasActiveSubscription());
        $this->assertEquals($subscription->remainingDays(), 14);
    }

    /**
     * @group basic
     */
    public function testUpgradeWithWrongDuration()
    {
        $subscription = $this->user->subscribeTo($this->plan, 15);
        sleep(1);

        $this->assertFalse($this->user->upgradeCurrentPlanTo($this->newPlan, 0));
        $this->assertFalse($this->user->upgradeCurrentPlanTo($this->newPlan, -1));
    }

    /**
     * @group basic
     */
    public function testUpgradeToWithInvalidDate()
    {
        $subscription = $this->user->subscribeTo($this->plan, 15);
        sleep(1);

        $this->assertFalse($this->user->upgradeCurrentPlanToUntil($this->plan, Carbon::yesterday()));
        $this->assertFalse($this->user->upgradeCurrentPlanToUntil($this->plan, Carbon::yesterday()->toDateTimeString()));
        $this->assertFalse($this->user->upgradeCurrentPlanToUntil($this->plan, Carbon::yesterday()->toDateString()));
    }

    /**
     * @group basic
     */
    public function testUpgradeToNow()
    {
        $subscription = $this->user->subscribeTo($this->plan, 15);
        sleep(1);

        $subscription = $this->user->upgradeCurrentPlanTo($this->newPlan, 30, true);

        $this->assertEquals($subscription->plan_id, $this->newPlan->id);
        $this->assertEquals($subscription->remainingDays(), 44);
    }

    /**
     * @group basic
     */
    public function testUpgradeToAnotherCycle()
    {
        $subscription = $this->user->subscribeTo($this->plan, 15);
        sleep(1);

        $this->user->upgradeCurrentPlanTo($this->newPlan, 30, false);

        $this->assertEquals($subscription->plan_id, $this->plan->id);
        $this->assertEquals($subscription->remainingDays(), 14);
        $this->assertEquals($this->user->subscriptions->count(), 2);
    }

    /**
     * @group basic
     */
    public function testUpgradeToNowWithCarbonInstance()
    {
        $subscription = $this->user->subscribeTo($this->plan, 15);
        sleep(1);

        $subscription = $this->user->upgradeCurrentPlanToUntil($this->newPlan, Carbon::now()->addDays(30), true);
        sleep(1);

        $this->assertEquals($subscription->plan_id, $this->newPlan->id);
        $this->assertEquals($subscription->remainingDays(), 29);
    }

    /**
     * @group basic
     */
    public function testUpgradeToAnotherCycleWithCarbonInstance()
    {
        $subscription = $this->user->subscribeTo($this->plan, 15);
        sleep(1);

        $this->user->upgradeCurrentPlanToUntil($this->newPlan, Carbon::now()->addDays(30), false);
        sleep(1);

        $this->assertEquals($subscription->plan_id, $this->plan->id);
        $this->assertEquals($subscription->remainingDays(), 14);
        $this->assertEquals($this->user->subscriptions->count(), 2);
    }

    /**
     * @group basic
     */
    public function testUpgradeToNowWithDateTimeString()
    {
        $subscription = $this->user->subscribeTo($this->plan, 15);
        sleep(1);

        $subscription = $this->user->upgradeCurrentPlanToUntil($this->newPlan, Carbon::now()->addDays(30)->toDateTimeString(), true);
        sleep(1);

        $this->assertEquals($subscription->plan_id, $this->newPlan->id);
        $this->assertEquals($subscription->remainingDays(), 29);
    }

    /**
     * @group basic
     */
    public function testUpgradeToAnotherCycleWithDateTimeString()
    {
        $subscription = $this->user->subscribeTo($this->plan, 15);
        sleep(1);

        $this->user->upgradeCurrentPlanToUntil($this->newPlan, Carbon::now()->addDays(30)->toDateTimeString(), false);
        sleep(1);

        $this->assertEquals($subscription->plan_id, $this->plan->id);
        $this->assertEquals($subscription->remainingDays(), 14);
        $this->assertEquals($this->user->subscriptions->count(), 2);
    }

    /**
     * @group basic
     */
    public function testUpgradeToNowWithDateString()
    {
        $subscription = $this->user->subscribeTo($this->plan, 15);
        sleep(1);

        $subscription = $this->user->upgradeCurrentPlanToUntil($this->newPlan, Carbon::now()->addDays(30)->toDateString(), true);
        sleep(1);

        $this->assertEquals($subscription->plan_id, $this->newPlan->id);
        $this->assertEquals($subscription->remainingDays(), 29);
    }

    /**
     * @group basic
     */
    public function testUpgradeToAnotherCycleWithDateString()
    {
        $subscription = $this->user->subscribeTo($this->plan, 15);
        sleep(1);

        $this->user->upgradeCurrentPlanToUntil($this->newPlan, Carbon::now()->addDays(30)->toDateString(), false);
        sleep(1);

        $this->assertEquals($subscription->plan_id, $this->plan->id);
        $this->assertEquals($subscription->remainingDays(), 14);
        $this->assertEquals($this->user->subscriptions->count(), 2);
    }

    /**
     * @group basic
     */
    public function testExtendWithWrongDuration()
    {
        $subscription = $this->user->subscribeTo($this->plan, 15);
        sleep(1);

        $this->assertFalse($this->user->extendCurrentSubscriptionWith(-1));
        $this->assertEquals($subscription->plan_id, $this->plan->id);
        $this->assertEquals($subscription->remainingDays(), 14);
    }

    /**
     * @group basic
     */
    public function testExtendNow()
    {
        $subscription = $this->user->subscribeTo($this->plan, 15);
        sleep(1);

        $subscription = $this->user->extendCurrentSubscriptionWith(30, true);
        sleep(1);

        $this->assertEquals($subscription->plan_id, $this->plan->id);
        $this->assertEquals($subscription->remainingDays(), 44);
    }

    /**
     * @group basic
     */
    public function testExtendToAnotherCycle()
    {
        $subscription = $this->user->subscribeTo($this->plan, 15);
        sleep(1);

        $this->user->extendCurrentSubscriptionWith(30, false);
        sleep(1);

        $this->assertEquals($subscription->plan_id, $this->plan->id);
        $this->assertEquals($subscription->remainingDays(), 14);
        $this->assertEquals($this->user->subscriptions->count(), 2);
    }

    /**
     * @group basic
     */
    public function testExtendNowWithCarbonInstance()
    {
        $subscription = $this->user->subscribeTo($this->plan, 15);
        sleep(1);

        $subscription = $this->user->extendCurrentSubscriptionUntil(Carbon::now()->addDays(30), true);
        sleep(1);

        $this->assertEquals($subscription->plan_id, $this->plan->id);
        $this->assertEquals($subscription->remainingDays(), 29);
    }

    /**
     * @group basic
     */
    public function testExtendToAnotherCycleWithCarbonInstance()
    {
        $subscription = $this->user->subscribeTo($this->plan, 15);
        sleep(1);

        $this->user->extendCurrentSubscriptionUntil(Carbon::now()->addDays(30), false);
        sleep(1);

        $this->assertEquals($subscription->plan_id, $this->plan->id);
        $this->assertEquals($subscription->remainingDays(), 14);
        $this->assertEquals($this->user->subscriptions->count(), 2);
    }

    /**
     * @group basic
     */
    public function testExtendNowWithDateTimeString()
    {
        $subscription = $this->user->subscribeTo($this->plan, 15);
        sleep(1);

        $subscription = $this->user->extendCurrentSubscriptionUntil(Carbon::now()->addDays(30)->toDateTimeString(), true);
        sleep(1);

        $this->assertEquals($subscription->plan_id, $this->plan->id);
        $this->assertEquals($subscription->remainingDays(), 29);
    }

    /**
     * @group basic
     */
    public function testExtendToAnotherCycleWithDateTimeString()
    {
        $subscription = $this->user->subscribeTo($this->plan, 15);
        sleep(1);

        $this->user->extendCurrentSubscriptionUntil(Carbon::now()->addDays(30)->toDateTimeString(), false);
        sleep(1);

        $this->assertEquals($subscription->plan_id, $this->plan->id);
        $this->assertEquals($subscription->remainingDays(), 14);
        $this->assertEquals($this->user->subscriptions->count(), 2);
    }

    /**
     * @group basic
     */
    public function testExtendNowWithDateString()
    {
        $subscription = $this->user->subscribeTo($this->plan, 15);
        sleep(1);

        $subscription = $this->user->extendCurrentSubscriptionUntil(Carbon::now()->addDays(30)->toDateString(), true);
        sleep(1);

        $this->assertEquals($subscription->plan_id, $this->plan->id);
        $this->assertEquals($subscription->remainingDays(), 29);
    }

    /**
     * @group basic
     */
    public function testExtendToAnotherCycleWithDateString()
    {
        $subscription = $this->user->subscribeTo($this->plan, 15);
        sleep(1);

        $this->user->extendCurrentSubscriptionUntil(Carbon::now()->addDays(30)->toDateString(), false);
        sleep(1);

        $this->assertEquals($subscription->plan_id, $this->plan->id);
        $this->assertEquals($subscription->remainingDays(), 14);
        $this->assertEquals($this->user->subscriptions->count(), 2);
    }

    /**
     * @group basic
     */
    public function testUpgradeFromUserWithoutActiveSubscription()
    {
        $subscription = $this->user->upgradeCurrentPlanTo($this->newPlan, 15, true);
        sleep(1);

        $this->assertEquals($subscription->plan_id, $this->newPlan->id);
        $this->assertEquals($subscription->remainingDays(), 14);
    }

    /**
     * @group basic
     */
    public function testUpgradeUntilFromUserWithoutActiveSubscription()
    {
        $subscription = $this->user->upgradeCurrentPlanToUntil($this->newPlan, Carbon::now()->addDays(15)->toDateString(), true);
        sleep(1);

        $this->assertEquals($subscription->plan_id, $this->newPlan->id);
        $this->assertEquals($subscription->remainingDays(), 14);
    }

    /**
     * @group basic
     */
    public function testUpgradeToFromUserNow()
    {
        $subscription = $this->user->subscribeTo($this->plan, 15);
        sleep(1);

        $subscription = $this->user->upgradeCurrentPlanTo($this->newPlan, 15, true);
        sleep(1);

        $this->assertEquals($subscription->plan_id, $this->newPlan->id);
        $this->assertEquals($subscription->remainingDays(), 29);
    }

    /**
     * @group basic
     */
    public function testUpgradeToFromUserToAnotherCycle()
    {
        $subscription = $this->user->subscribeTo($this->plan, 15);
        sleep(1);

        $this->user->upgradeCurrentPlanTo($this->newPlan, 30, false);
        sleep(1);

        $this->assertEquals($subscription->plan_id, $this->plan->id);
        $this->assertEquals($subscription->remainingDays(), 14);
        $this->assertEquals($this->user->subscriptions->count(), 2);
    }

    /**
     * @group basic
     */
    public function testExtendFromUserWithoutActiveSubscription()
    {
        $subscription = $this->user->extendCurrentSubscriptionWith(15, true);
        sleep(1);

        $this->assertEquals($subscription->plan_id, $this->plan->id);
        $this->assertEquals($subscription->remainingDays(), 14);
    }

    /**
     * @group basic
     */
    public function testExtendFromUserNow()
    {
        $subscription = $this->user->subscribeTo($this->plan, 15);
        sleep(1);

        $subscription = $this->user->extendCurrentSubscriptionWith(15, true);
        sleep(1);

        $this->assertEquals($subscription->plan_id, $this->plan->id);
        $this->assertEquals($subscription->remainingDays(), 29);
    }

    /**
     * @group basic
     */
    public function testExtendFromUserToAnotherCycle()
    {
        $subscription = $this->user->subscribeTo($this->plan, 15);
        sleep(1);

        $this->user->extendCurrentSubscriptionWith(15, false);
        sleep(1);

        $this->assertEquals($subscription->plan_id, $this->plan->id);
        $this->assertEquals($subscription->remainingDays(), 14);
        $this->assertEquals($this->user->subscriptions->count(), 2);
    }

    /**
     * @group basic
     */
    public function testCancelSubscription()
    {
        $subscription = $this->user->subscribeTo($this->plan, 15);
        sleep(1);

        $this->assertEquals($subscription->plan_id, $this->plan->id);
        $this->assertEquals($subscription->remainingDays(), 14);

        $subscription = $this->user->cancelCurrentSubscription();
        sleep(1);

        $this->assertNotNull($subscription);
        $this->assertTrue($subscription->isCancelled());
        $this->assertTrue($subscription->isPendingCancellation());
        $this->assertFalse($this->user->cancelCurrentSubscription());
        $this->assertEquals($this->user->subscriptions()->cancelled()->count(), 1);
    }

    /**
     * @group basic
     */
    public function testCancelSubscriptionFromUser()
    {
        $subscription = $this->user->subscribeTo($this->plan, 15);
        sleep(1);

        $this->assertEquals($subscription->plan_id, $this->plan->id);
        $this->assertEquals($subscription->remainingDays(), 14);

        $subscription = $this->user->cancelCurrentSubscription();
        sleep(1);

        $this->assertNotNull($subscription);
        $this->assertTrue($subscription->isCancelled());
        $this->assertTrue($subscription->isPendingCancellation());
        $this->assertFalse($this->user->cancelCurrentSubscription());
    }

    /**
     * @group basic
     */
    public function testCancelSubscriptionWithoutSubscription()
    {
        $this->assertFalse($this->user->cancelCurrentSubscription());
    }
}
