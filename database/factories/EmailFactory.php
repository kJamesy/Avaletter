<?php

$factory->define(App\Email::class, function (Faker\Generator $faker) {
    $user = \App\User::first();
    $editionsArr = \App\EmailEdition::pluck('id')->toArray();

    if ( $user && $editionsArr ) {
        $editionId = $editionsArr[rand(0, (count($editionsArr) - 1))];

        return [
            'user_id' => $user->id,
            'email_edition_id' => $editionId,
            'from' => "$user->first_name $user->last_name <$user->email>",
            'subject' => $faker->sentence,
            'body' => $faker->text,
            'is_deleted' => 0,
            'is_draft' => 0,
            'send_success' => 1,
            'sent_at' => \Carbon\Carbon::now()
        ];
    }
});
