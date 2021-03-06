<?php

namespace App\Mail;

use App\Email;
use GuzzleHttp\Client;
use Html2Text\Html2Text;
use Http\Adapter\Guzzle6\Client as GuzzleAdapter;
use Illuminate\Database\Eloquent\Collection;
use SparkPost\SparkPost;

class Newsletter
{
    public $sparkSecret;
    public $sender;
    public $subject;
    public $body;
    public $recipients;
    public $variables;

    /**
     * Newsletter constructor.
     * @param Email $email
     * @param Collection $recipients
     */
    public function __construct(Email $email, Collection $recipients)
    {
        $this->sparkSecret = env('SPARKPOST_SECRET');
        $this->sender = $this->buildSender($email->from);
        $this->subject = $email->subject;
        $this->variables = $this->defineEmailVariables();
        $this->emailId = $email->id;
        $this->body = $this->replaceEmailVariables($email->body);
        $this->recipients = $this->buildRecipientsArray($recipients);
    }

    /**
     * Fire email via Sparkpost
     * @return array
     */
    public function fireEmail()
    {
        $httpClient = new GuzzleAdapter(new Client());
        $sparky = new SparkPost($httpClient, ['key' => $this->sparkSecret]);
        $sparky->setOptions(['async' => false]);

        $promise = $sparky->transmissions->post($this->getSparkyContent());

        try {
            $response = $sparky->transmissions->get();
            return ['success' => $response];
        }
        catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }

    }

    /**
     * Construct content for SparkPost
     * @return array
     */
    public function getSparkyContent()
    {
        $html = "<html><body>$this->body</body></html>";
        $html2Text = new Html2Text($html);
        $text = $html2Text->getText();

        return [
            'campaign_id' => "$this->emailId",
            'content' => [
                'from' => $this->sender,
                'subject' => $this->subject,
                'html' => $html,
                'text' => $text
            ],
            'recipients' => $this->recipients,
        ];
    }
    /**
     * Get sender details
     * @param $raw
     * @return array
     */
    protected function buildSender($raw)
    {
        $exploded = explode('<', $raw);

        $name = trim($exploded[0]);
        $email = trim(explode('>', $exploded[1])[0]);

        return compact('name', 'email');
    }

    /**
     * Define email variables to substitute
     * Ensure the array key is a property of App\Subscriber; except unsubscribe_link
     * @return array
     */
    protected function defineEmailVariables()
    {
        return [
            'id' => '%id%',
            'first_name' => '%first_name%',
            'last_name' => '%last_name%',
            'email' => '%email%',
            'unsubscribe_link' => '%unsubscribe_link%'
        ];
    }

    /**
     * Wrap user variables with the SparkPost format
     * @param $body
     * @return mixed
     */
    protected function replaceEmailVariables($body)
    {
        $variables = $this->variables;

        if ( count($variables) ) {
            foreach ($variables as $key => $variable) {
                $body = ( $key == 'unsubscribe_link' )
                    ? str_ireplace($variable, "{{{ $key }}}", $body)
                    : str_ireplace($variable, "{{ $key }}", $body);

            }
        }

        return $body;
    }

    /**
     * Get recipient address details
     * @param $recipient
     * @return array
     */
    protected function buildRecipientAddress($recipient)
    {
        return [
            'name' => "$recipient->first_name $recipient->last_name",
            'email' => $recipient->email
        ];
    }

    /**
     * Get recipient's substitution data
     * @param $recipient
     * @return array
     */
    protected function getRecipientSubstitutionData($recipient)
    {
        $variables = $this->variables;
        $data = [];

        if ( count($variables) ) {
            foreach ($variables as $key => $variable) {
                $data[$key] = ($key == 'unsubscribe_link')
                    ? "<a href='" . route('subscribers.unsubscribe') . "?email=$recipient->email' data-msys-unsubscribe='1'>unsubscribe</a>"
                    : $recipient->{$key};
            }
        }

        return $data;
    }

    /**
     * Get recipients array
     * @param $recipients
     * @return array
     */
    protected function buildRecipientsArray($recipients)
    {
        $recipientsArr = [];

        foreach($recipients as $recipient) {
            $recipientsArr[] = [
                'address' => $this->buildRecipientAddress($recipient),
                'substitution_data' => $this->getRecipientSubstitutionData($recipient),
            ];
        }

        return $recipientsArr;
    }


}
