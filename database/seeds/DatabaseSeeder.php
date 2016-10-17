<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
//        factory(\App\MailingList::class, 30)->create();

        factory(\App\Subscriber::class, 1500)->create()->each(function($subscriber) {
            $subscriber->mailing_lists()->sync(
                \App\MailingList::all()->random(2)
            );
        });
    }
}
