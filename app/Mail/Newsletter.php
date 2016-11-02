<?php

namespace App\Mail;

use App\EmailTemplate;
use App\Subscriber;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class Newsletter extends Mailable
{
    use Queueable, SerializesModels;

    protected $email;
    protected $variables;
    public $subscriber;

    /**
     * Create a new message instance.
     *
     * @param EmailTemplate $template
     * @param Subscriber $subscriber
     */
    public function __construct(EmailTemplate $template, Subscriber $subscriber)
    {
        $this->email = $template;
        $this->subscriber = $subscriber;
        $this->variables = ['id' => '%recipient.id%', 'first_name' => '%recipient.first_name%', 'last_name' => '%recipient.last_name%', 'email' => '%recipient.email%'];
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $email = $this->replaceEmailVariables();
        return $this->view('newsletter.subscriber')->with(compact('email'));
    }


    /**
     * Handle user variables
     * @return EmailTemplate|mixed
     */
    protected function replaceEmailVariables()
    {
        $email = $this->email;
        $variables = $this->variables;
        $subscriber = $this->subscriber;

        if ( count($variables) ) {
            foreach ($variables as $key => $variable)
                $email = str_replace($variable, $subscriber->{$key}, $email);
        }

        return $email;
    }
}
