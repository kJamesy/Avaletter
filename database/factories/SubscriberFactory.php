<?php

$factory->define(App\Subscriber::class, function (Faker\Generator $faker) {
    return [
        'first_name' => $faker->firstName,
        'last_name' => $faker->lastName,
        'email' => $faker->email,
        'active' => $faker->boolean,
        'is_deleted' => $faker->boolean
    ];
});
