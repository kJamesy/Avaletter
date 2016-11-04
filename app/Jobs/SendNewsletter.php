<?php

namespace App\Jobs;

use App\Email;
use App\Mail\Newsletter;
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
        file_put_contents('test.html', $this->email);
//        if ( $this->email && count($this->recipients) ) {
//            $newsletter = new Newsletter($this->email, $this->recipients);
//            $newsletter->fireEmail();
//        }
    }
}
