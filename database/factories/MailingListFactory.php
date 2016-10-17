<?php

$factory->define(App\MailingList::class, function (Faker\Generator $faker) {
    return [
        'name' => ucfirst($faker->text(16))
    ];
});
