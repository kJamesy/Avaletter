<?php

$factory->define(App\EmailInjection::class, function (Faker\Generator $faker) {
    return [
        'injected_at' => \Carbon\Carbon::now()->subMinutes(rand(1, 10))
    ];
});
