<?php
/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(\Rennokki\Plans\Models\PlanModel::class, function () {
    return [
        'name' => 'Testing Plan '.str_random(7),
        'description' => 'This is a testing plan.',
        'price' => (float) mt_rand(10, 200),
        'currency' => 'EUR',
        'duration' => 30,
    ];
});
