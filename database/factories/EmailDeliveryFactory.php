<?php

$factory->define(App\EmailDelivery::class, function (Faker\Generator $faker) {
    return [
        'delivered_at' => \Carbon\Carbon::now()->subMinutes(rand(11, 15))
    ];
});
