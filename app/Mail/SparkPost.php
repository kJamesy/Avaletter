<?php

namespace App\Mail;

use App\EmailTemplate;
use App\Subscriber;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SparkPost extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    protected $content;
    protected $variables;
    protected $subscriber;

    /**
     * Create a new message instance.
     *
     * @param EmailTemplate $template
     * @param Subscriber $subscriber
     */
    public function __construct(EmailTemplate $template, Subscriber $subscriber)
    {
        $this->content = $template->content;
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
        app('swift.transport')->driver('sparkpost')->setOptions([
            'open_tracking' => false,
            'click_tracking' => false,
            'transactional' => true,
        ]);
        
        $content = $this->replaceEmailVariables();
        return $this->view('newsletter.subscriber')->with(compact('content'));
    }


    /**
     * Handle user variables
     * @return EmailTemplate|mixed
     */
    protected function replaceEmailVariables()
    {
        $content = $this->content;
        $variables = $this->variables;
        $subscriber = $this->subscriber;

        if ( count($variables) ) {
            foreach ($variables as $key => $variable)
                $content = str_ireplace($variable, $subscriber->{$key}, $content);
        }

        return $content;
    }
}
