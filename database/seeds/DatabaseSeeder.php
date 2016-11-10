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
//        $this->createEmailClicks();

    }

    /**
     * Create Email Clicks
     */
    protected function createEmailClicks()
    {
        $injectionsArr = \App\EmailInjection::pluck('id')->toArray();

        if ( $injectionsArr ) {
            $injectionsIndices = count($injectionsArr) - 1;

            for ( $i = 0; $i < 500; $i++ ) {
                $injectionId = $injectionsArr[rand(0, $injectionsIndices)];
                $target_url = \Faker\Factory::create()->url;

                if ( $injectionId && $target_url ) {
                    $exists = \App\EmailClick::where('email_injection_id', $injectionId)->where('target_link', $target_url)->first();

                    if ( $exists )
                        $i -= 1;
                    else
                        factory(\App\EmailClick::class, 1)->create(['email_injection_id' => $injectionId, 'target_link' => $target_url]);
                }
            }
        }
    }

    /**
     * Create Email Deliveries
     */
    protected function createEmailOpens()
    {
        $injectionsArr = \App\EmailInjection::pluck('id')->toArray();

        if ( $injectionsArr ) {
            $injectionsIndices = count($injectionsArr) - 1;

            for ( $i = 0; $i < 837; $i++ ) {
                $injectionId = $injectionsArr[rand(0, $injectionsIndices)];

                if ( $injectionId ) {
                    $exists = \App\EmailOpen::where('email_injection_id', $injectionId)->first();

                    if ($exists)
                        $i -= 1;
                    else
                        factory(\App\EmailOpen::class, 1)->create(['email_injection_id' => $injectionId]);
                }
            }
        }
    }

    /**
     * Create Email Deliveries
     */
    protected function createEmailDeliveries()
    {
        $injectionsArr = \App\EmailInjection::pluck('id')->toArray();

        if ( $injectionsArr ) {
            $injectionsIndices = count($injectionsArr) - 1;

            for ( $i = 0; $i < 1200; $i++ ) {
                $injectionId = $injectionsArr[rand(0, $injectionsIndices)];

                if ( $injectionId ) {
                    $exists = \App\EmailDelivery::where('email_injection_id', $injectionId)->first();

                    if ($exists)
                        $i -= 1;
                    else
                        factory(\App\EmailDelivery::class, 1)->create(['email_injection_id' => $injectionId]);
                }
            }
        }
    }

    /**
     * Create Email Injections
     */
    protected function createEmailInjections()
    {
        $emailIdsArr = \App\Email::where('is_deleted', 0)->where('is_draft', 0)->where('send_success', 1)->whereNotNull('sent_at')->pluck('id')->toArray();
        $subscriberIdsArr = \App\Subscriber::where('active', 1)->where('is_deleted', 0)->pluck('id')->toArray();

        if ( $emailIdsArr && $subscriberIdsArr ) {
            $emailIdsIndices = count($emailIdsArr) - 1;
            $subscriberIdsIndices = count($subscriberIdsArr) - 1;

            for ( $i = 0; $i < 1500; $i++ ) {
                $emailId = $emailIdsArr[rand(0, $emailIdsIndices)];
                $subscriberId = $subscriberIdsArr[rand(0, $subscriberIdsIndices)];

                if ($emailId && $subscriberId) {
                    $exists = \App\EmailInjection::where('email_id', $emailId)->where('subscriber_id', $subscriberId)->first();

                    if ($exists)
                        $i -= 1;
                    else
                        factory(\App\EmailInjection::class, 1)->create(['email_id' => $emailId, 'subscriber_id' => $subscriberId]);
                }
            }
        }
    }

    /**
     * Create subscribers
     */
    protected function createSubscribers()
    {
        factory(\App\Subscriber::class, 1000)->create()->each(function($subscriber) {
            $subscriber->mailing_lists()->sync(
                \App\MailingList::all()->random(2)
            );
        });
    }
}
