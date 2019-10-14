<?php

return [

    /*
     * The model which handles the plans tables.
     */

    'models' => [

        'plan' => \Creatydev\Plans\Models\PlanModel::class,
        'subscription' => \Creatydev\Plans\Models\PlanSubscriptionModel::class,
        'feature' => \Creatydev\Plans\Models\PlanFeatureModel::class,
        'usage' => \Creatydev\Plans\Models\PlanSubscriptionUsageModel::class,

        'stripeCustomer' => \Creatydev\Plans\Models\StripeCustomerModel::class,

    ],
    /*
     * Payment methods
     */
    'payment_methods' => ['stripe'],

];
