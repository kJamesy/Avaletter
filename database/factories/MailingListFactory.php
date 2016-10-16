<?php

$factory->define(App\MailingList::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->text(48)
    ];
});
