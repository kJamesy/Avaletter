<?php

namespace App\Jobs;

use App\Email;
use App\Mail\Newsletter;
use App\SparkyResponse;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendNewsletter implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $email;
    protected $recipients;

    /**
     * SendNewsletter constructor.
     * @param Email $email
     * @param Collection $recipients
     */
    public function __construct(Email $email, Collection $recipients)
    {
        $this->email = $email;
        $this->recipients = $recipients;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ( $this->email && count($this->recipients) ) {
            $newsletter = new Newsletter($this->email, $this->recipients);
            $feedback = $newsletter->fireEmail();

            if ( is_array($feedback) && array_key_exists('success', $feedback) ) {
                $this->email->send_success = 1;
                $this->email->sent_at = Carbon::now();
            }
            else {
                $this->email->send_success = 0;
                $this->email->sent_at = null;
            }

            $this->email->save();
        }
    }
}
