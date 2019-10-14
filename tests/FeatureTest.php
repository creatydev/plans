<?php

namespace Rennokki\Plans\Test;

use Rennokki\Plans\Models\PlanFeatureModel;

class FeatureTest extends TestCase
{
    protected $user;
    protected $plan;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = factory(\Rennokki\Plans\Test\Models\User::class)->create();
        $this->plan = factory(\Rennokki\Plans\Models\PlanModel::class)->create();
    }

    /**
     * @group basic
     */
    public function testConsumeFeature()
    {
        $subscription = $this->user->subscribeTo($this->plan, 30);

        $subscription->features()->saveMany([
            new PlanFeatureModel([
                'name' => 'Build minutes',
                'code' => 'build.minutes',
                'description' => 'Build minutes used for CI/CD.',
                'type' => 'limit',
                'limit' => 2000,
            ]),
            new PlanFeatureModel([
                'name' => 'Vault access',
                'code' => 'vault.access',
                'description' => 'Access to the precious vault.',
                'type' => 'feature',
            ]),
            new PlanFeatureModel([
                'name' => 'Users amount',
                'code' => 'users.amount',
                'description' => 'The maximum amount of users than can use the app at the same time.',
                'type' => 'limit',
                'limit' => -1,
            ]),
        ]);

        $this->assertEquals($this->plan->features()->limited()->count(), 2);
        $this->assertEquals($this->plan->features()->feature()->count(), 1);

        $this->assertEquals($subscription->features()->count(), 3);
        $this->assertEquals($subscription->usages()->count(), 0);

        $this->assertFalse($subscription->consumeFeature('build.minutes', 2001));
        $this->assertEquals($subscription->usages()->count(), 1);
        $this->assertFalse($subscription->consumeFeature('build.hours', 1));
        $this->assertTrue($subscription->consumeFeature('build.minutes', 10));
        $this->assertTrue($subscription->consumeFeature('build.minutes', 20));
        $this->assertEquals($subscription->usages()->where('code', 'build.minutes')->first()->used, 30);

        $this->assertTrue($subscription->consumeFeature('users.amount', 1));
        $this->assertTrue($subscription->consumeFeature('users.amount', 100));
        $this->assertTrue($subscription->consumeFeature('users.amount', 300));
        $this->assertTrue($subscription->consumeFeature('users.amount', 1000));
        $this->assertTrue($subscription->consumeFeature('users.amount', 10000));

        $this->assertEquals($subscription->usages()->where('code', 'users.amount')->first()->used, 11401);
        $this->assertEquals($subscription->getUsageOf('users.amount'), 11401);
        $this->assertEquals($subscription->getRemainingOf('users.amount'), -1);
        $this->assertNotNull($subscription->usages()->where('code', 'users.amount')->first());

        $this->assertFalse($subscription->consumeFeature('vault.access', 2001));

        $this->assertNull($subscription->getUsageOf('vault.access'));
        $this->assertEquals($subscription->getRemainingOf('vault.access'), 0);

        $this->assertNull($subscription->getUsageOf('build.hours'));
        $this->assertEquals($subscription->getRemainingOf('build.hours'), 0);
    }

    /**
     * @group basic
     */
    public function testUnconsumeFeature()
    {
        $subscription = $this->user->subscribeTo($this->plan, 30);

        $subscription->features()->saveMany([
            new PlanFeatureModel([
                'name' => 'Build minutes',
                'code' => 'build.minutes',
                'description' => 'Build minutes used for CI/CD.',
                'type' => 'limit',
                'limit' => 2000,
            ]),
            new PlanFeatureModel([
                'name' => 'Vault access',
                'code' => 'vault.access',
                'description' => 'Access to the precious vault.',
                'type' => 'feature',
            ]),
            new PlanFeatureModel([
                'name' => 'Users amount',
                'code' => 'users.amount',
                'description' => 'The maximum amount of users than can use the app at the same time.',
                'type' => 'limit',
                'limit' => -1,
            ]),
        ]);

        $this->assertEquals($this->plan->features()->limited()->count(), 2);
        $this->assertEquals($this->plan->features()->feature()->count(), 1);

        $this->assertTrue($subscription->consumeFeature('build.minutes', 30));
        $this->assertEquals($subscription->usages()->where('code', 'build.minutes')->first()->used, 30);
        $this->assertEquals($subscription->getUsageOf('build.minutes'), 30);
        $this->assertEquals($subscription->getRemainingOf('build.minutes'), 1970);

        $this->assertEquals($subscription->features()->count(), 3);
        $this->assertEquals($subscription->usages()->count(), 1);

        $this->assertTrue($subscription->unconsumeFeature('build.minutes', 20));
        $this->assertEquals($subscription->usages()->where('code', 'build.minutes')->first()->used, 10);
        $this->assertEquals($subscription->getUsageOf('build.minutes'), 10);
        $this->assertEquals($subscription->getRemainingOf('build.minutes'), 1990);
        $this->assertTrue($subscription->unconsumeFeature('build.minutes', 10));
        $this->assertEquals($subscription->usages()->where('code', 'build.minutes')->first()->used, 0);
        $this->assertEquals($subscription->getUsageOf('build.minutes'), 0);
        $this->assertEquals($subscription->getRemainingOf('build.minutes'), 2000);

        $this->assertTrue($subscription->unconsumeFeature('users.amount', 1));
        $this->assertTrue($subscription->unconsumeFeature('users.amount', 100));
        $this->assertTrue($subscription->unconsumeFeature('users.amount', 300));
        $this->assertTrue($subscription->unconsumeFeature('users.amount', 1000));
        $this->assertTrue($subscription->unconsumeFeature('users.amount', 10000));

        $this->assertEquals($subscription->usages()->where('code', 'users.amount')->first()->used, 0);
        $this->assertEquals($subscription->getUsageOf('users.amount'), 0);
        $this->assertEquals($subscription->getRemainingOf('users.amount'), -1);
        $this->assertNotNull($subscription->usages()->where('code', 'users.amount')->first());
        $this->assertNotNull($subscription->usages()->where('code', 'users.amount')->first());

        $this->assertFalse($subscription->unconsumeFeature('vault.access', 20));
    }
}
