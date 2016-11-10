<?php

$factory->define(App\EmailClick::class, function (Faker\Generator $faker) {
    return [
//        'target_link' => $faker->url,
        'ip_address' => $faker->ipv4,
        'country' => $faker->countryCode,
        'user_agent' => $faker->userAgent,
        'device' => ['Desktop', 'Mobile', 'Other'][$faker->numberBetween(0, 2)],
        'platform' => [$faker->macPlatformToken, $faker->windowsPlatformToken][$faker->numberBetween(0,1)],
        'browser' => ['Firefox', 'IE', 'Safari', 'Outlook 2012', 'Outlook 2016', 'Chrome'][$faker->numberBetween(0, 5)],
        'hits' => $faker->numberBetween(1, 5),
        'clicked_at' => \Carbon\Carbon::now()->subMinutes(rand(5, 10))
    ];
});
