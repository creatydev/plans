<?php

namespace Rennokki\Plans\Test;

use Stripe\Stripe;
use Stripe\Token as StripeToken;
use Rennokki\Plans\Models\PlanModel;
use Rennokki\Plans\Test\Models\User;
use Rennokki\Plans\Models\PlanFeatureModel;
use Orchestra\Testbench\TestCase as Orchestra;
use Rennokki\Plans\Models\StripeCustomerModel;
use Rennokki\Plans\Models\PlanSubscriptionModel;
use Rennokki\Plans\Models\PlanSubscriptionUsageModel;

abstract class TestCase extends Orchestra
{
    protected $invalidStripeToken = 'tok_chargeDeclinedInsufficientFunds';

    public function setUp(): void
    {
        parent::setUp();

        $this->resetDatabase();

        $this->loadLaravelMigrations(['--database' => 'sqlite']);
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->withFactories(__DIR__.'/../database/factories');

        $this->artisan('migrate', ['--database' => 'sqlite']);
    }

    protected function getPackageProviders($app)
    {
        return [
            \Rennokki\Plans\PlansServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => __DIR__.'/database.sqlite',
            'prefix' => '',
        ]);
        $app['config']->set('auth.providers.users.model', User::class);
        $app['config']->set('app.key', 'wslxrEFGWY6GfGhvN9L3wH3KSRJQQpBD');
        $app['config']->set('plans.models.plan', PlanModel::class);
        $app['config']->set('plans.models.feature', PlanFeatureModel::class);
        $app['config']->set('plans.models.subscription', PlanSubscriptionModel::class);
        $app['config']->set('plans.models.usage', PlanSubscriptionUsageModel::class);
        $app['config']->set('plans.models.stripeCustomer', StripeCustomerModel::class);
    }

    protected function resetDatabase()
    {
        file_put_contents(__DIR__.'/database.sqlite', null);
    }

    protected function initiateStripeAPI()
    {
        return Stripe::setApiKey($_ENV['STRIPE_SECRET']);
    }

    protected function getStripeTestToken()
    {
        $this->initiateStripeAPI();

        $token = StripeToken::create([
            'card' => [
                'number' => '4242424242424242',
                'exp_month' => 1,
                'exp_year' => 2030,
                'cvc' => '999',
            ],
        ]);

        return $token->id;
    }

    protected function getInvalidStripeToken()
    {
        return $this->invalidStripeToken;
    }
}
