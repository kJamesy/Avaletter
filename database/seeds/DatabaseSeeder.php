<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Do one at a time
     * @return void
     */
    public function run()
    {
//        $this->createSubscribers(5000);
//        $this->createEmailInjections();
//        $this->createEmailDeliveries();
//        $this->createEmailOpens();
//        $this->createEmailClicks();

    }

    /**
     * Create Email Clicks
     * @param int $emailId
     * @param int $num
     */
    protected function createEmailClicks($emailId = 0, $num = 2000)
    {
        $injectionsArr = $emailId ? \App\EmailInjection::where('email_id', $emailId)->pluck('id')->toArray() : \App\EmailInjection::pluck('id')->toArray();

        if ( $injectionsArr ) {
            $injectionsIndices = count($injectionsArr) - 1;

            for ( $i = 0; $i < $num; $i++ ) {
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
     * @param int $emailId
     * @param int $num
     */
    protected function createEmailOpens($emailId = 0, $num = 3000)
    {
        $injectionsArr = $emailId ? \App\EmailInjection::where('email_id', $emailId)->pluck('id')->toArray() : \App\EmailInjection::pluck('id')->toArray();

        if ( $injectionsArr ) {
            $injectionsIndices = count($injectionsArr) - 1;

            for ( $i = 0; $i < $num; $i++ ) {
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
     * @param int $emailId
     * @param int $num
     */
    protected function createEmailDeliveries($emailId = 0, $num = 4500)
    {
        $injectionsArr = $emailId ? \App\EmailInjection::where('email_id', $emailId)->pluck('id')->toArray() : \App\EmailInjection::pluck('id')->toArray();

        if ( $injectionsArr ) {
            $injectionsIndices = count($injectionsArr) - 1;

            for ( $i = 0; $i < $num; $i++ ) {
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
     * @param array $emailIds
     * @param int $num
     */
    protected function createEmailInjections($emailIds = [], $num = 5000)
    {
        $emailIdsArr = $emailIds ?: \App\Email::where('is_deleted', 0)->where('is_draft', 0)->where('send_success', 1)->whereNotNull('sent_at')->pluck('id')->toArray();
        $subscriberIdsArr = \App\Subscriber::where('active', 1)->where('is_deleted', 0)->pluck('id')->toArray();

        if ( $emailIdsArr && $subscriberIdsArr ) {
            $emailIdsIndices = count($emailIdsArr) - 1;
            $subscriberIdsIndices = count($subscriberIdsArr) - 1;

            for ( $i = 0; $i < $num; $i++ ) {
                $emailId = ( count($emailIds) == 1 ) ? $emailIds[0] : $emailIdsArr[rand(0, $emailIdsIndices)];
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
     * Create Sent Email
     */
    protected function createSentEmail()
    {
        factory(\App\Email::class, 1)->create();
    }

    /**
     * Create subscribers
     * @param int $num
     */
    protected function createSubscribers($num = 5000)
    {
        $mailingLists = \App\MailingList::pluck('id')->toArray();
        $existingEmails = \App\Subscriber::pluck('email')->toArray();

        for ( $i = 0; $i < $num; $i++ ) {
            $mailingListsIndices = count($mailingLists) - 1;
            $email = \Faker\Factory::create()->safeEmail;
            $exists = in_array($email, $existingEmails);

            if ($exists)
                $i -= 1;
            else {
                factory(\App\Subscriber::class, 1)->create(['email' => $email])->each(function ($subscriber) use ($mailingLists, $mailingListsIndices) {
                    $subscriber->mailing_lists()->sync(
                        [$mailingLists[rand(0, $mailingListsIndices)]]
                    );
                });
            }
        }

    }
}
