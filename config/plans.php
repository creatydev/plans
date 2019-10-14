<?php

return [

    /*
     * The model which handles the plans tables.
     */

    'models' => [

        'plan' => \Rennokki\Plans\Models\PlanModel::class,
        'subscription' => \Rennokki\Plans\Models\PlanSubscriptionModel::class,
        'feature' => \Rennokki\Plans\Models\PlanFeatureModel::class,
        'usage' => \Rennokki\Plans\Models\PlanSubscriptionUsageModel::class,

        'stripeCustomer' => \Rennokki\Plans\Models\StripeCustomerModel::class,

    ],
    /*
     * Payment methods
     */
    'payment_methods' => ['stripe'],

];
