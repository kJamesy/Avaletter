<?php

namespace App\AvaHelper;

use App\Email;
use App\EmailClick;
use App\EmailDelivery;
use App\EmailInjection;
use App\EmailOpen;
use App\Subscriber;
use Carbon\Carbon;
use Jenssegers\Agent\Agent;

class EmailEventsHandler
{
    protected $sparkPostResponse;
    protected $agent;

    /**
     * EmailEventsHelper constructor.
     * @param $sparkPostResponse
     */
    public function __construct($sparkPostResponse)
    {
        $this->sparkPostResponse = (array) $sparkPostResponse;
        $this->agent = new Agent();
    }

    /**
     * Handle the Events and push into DB
     */
    public function handleEvents()
    {
        foreach( $this->sparkPostResponse as $response ) {
            $response = $this->getValueOfProperty($response, 'msys');
            $event = (object) [];

            if ( is_object($response) ) {
                if ( property_exists($response, 'message_event') )
                    $event = $response->message_event;
                elseif ( property_exists($response, 'track_event') )
                    $event = $response->track_event;
            }

            $eventType = strtolower( $this->getValueOfProperty($event, 'type') );
            $recipientEmail = trim( $this->getValueOfProperty($event, 'rcpt_to') );
            $emailId = (int) $this->getValueOfProperty($event, 'campaign_id');
            $time = Carbon::createFromTimestampUTC( $this->getValueOfProperty($event, 'timestamp') );
            $injection = $this->getOrCreateInjection($recipientEmail, $emailId, $time);

            if ( $eventType == 'injection' ) {} //Do no more

            elseif ( $eventType == 'delivery' && $injection ) {
                $delivery = $this->createDelivery($injection->id, $time);
            }

            elseif ( $eventType == 'open' && $injection ) {
                $open = $this->createOpen($injection->id, $time, $event);
            }

            elseif ( $eventType == 'click' && $injection ) {
                $click = $this->createClick($injection->id, $time, $event);
            }
        }
    }

    /**
     * Get injection or create new injection
     * @param $recipientEmail
     * @param $emailId
     * @param Carbon $time
     * @return EmailInjection|mixed|null
     */
    protected function getOrCreateInjection($recipientEmail, $emailId, Carbon $time)
    {
        $email = Email::getEmailById( $emailId );
        $subscriber = Subscriber::getSubscriberByEmail( $recipientEmail );
        $injection = null;

        if ( $email && $subscriber ) {
            $injection = EmailInjection::getEmailInjection($email->id, $subscriber->id);

            if ( ! $injection ) {
                $injection = new EmailInjection();
                $injection->email_id = $email->id;
                $injection->subscriber_id = $subscriber->id;
                $injection->injected_at = $time;
                $injection->save();
            }
        }

        return $injection;
    }

    /**
     * Create a new delivery or return existing
     * @param $injectionId
     * @param Carbon $time
     * @return EmailDelivery|mixed
     */
    protected function createDelivery($injectionId, Carbon $time)
    {
        $delivery = EmailDelivery::findDeliveryByInjectionId($injectionId);

        if ( ! $delivery ) {
            $delivery = new EmailDelivery();
            $delivery->email_injection_id = $injectionId;
            $delivery->delivered_at = $time;
            $delivery->save();
        }

        return $delivery;
    }

    /**
     * Create / Modify an open and return it
     * @param $injectionId
     * @param Carbon $time
     * @param $eventBag
     * @return EmailOpen|mixed
     */
    protected function createOpen($injectionId, Carbon $time, $eventBag)
    {
        $ip_address = $this->getValueOfProperty($eventBag, 'ip_address');
        $country = $this->getValueOfProperty($this->getValueOfProperty($eventBag, 'geo_ip'), 'country');
        $user_agent = $this->getValueOfProperty($eventBag, 'user_agent');
        $device = $this->getDevice($user_agent);
        $platform = $this->getPlatform($user_agent);
        $browser = $this->getBrowser($user_agent);

        $open = EmailOpen::findOpenByInjectionId($injectionId);
        $hits = $open ? (int) $open->hits + 1 : 1;

        if ( ! $open )
            $open = new EmailOpen();

        $open->email_injection_id = $injectionId;
        $open->ip_address = $ip_address;
        $open->country = $country;
        $open->user_agent = $user_agent;
        $open->device = $device;
        $open->platform = $platform;
        $open->browser = $browser;
        $open->hits = $hits;
        $open->opened_at = $time;
        $open->save();

        return $open;
    }

    /**
     * Create / Modify a click and return it
     * @param $injectionId
     * @param Carbon $time
     * @param $eventBag
     * @return EmailClick|mixed
     */
    protected function createClick($injectionId, Carbon $time, $eventBag)
    {
        $target_link = $this->getValueOfProperty($eventBag, 'target_link_url');
        $ip_address = $this->getValueOfProperty($eventBag, 'ip_address');
        $country = $this->getValueOfProperty($this->getValueOfProperty($eventBag, 'geo_ip'), 'country');
        $user_agent = $this->getValueOfProperty($eventBag, 'user_agent');
        $device = $this->getDevice($user_agent);
        $platform = $this->getPlatform($user_agent);
        $browser = $this->getBrowser($user_agent);

        $click = EmailClick::findClick($injectionId, $target_link);
        $hits = $click ? (int) $click->hits + 1 : 1;

        if ( ! $click )
            $click = new EmailClick();

        $click->email_injection_id = $injectionId;
        $click->target_link = $target_link;
        $click->ip_address = $ip_address;
        $click->country = $country;
        $click->user_agent = $user_agent;
        $click->device = $device;
        $click->platform = $platform;
        $click->browser = $browser;
        $click->hits = $hits;
        $click->clicked_at = $time;
        $click->save();

        return $click;
    }

    /**
     * Get the value of an object property
     * @param $obj
     * @param $prop
     * @return mixed|null
     */
    protected function getValueOfProperty($obj, $prop)
    {
        return ( is_object($obj) && property_exists( $obj, $prop) ) ? $obj->{$prop} :  null;
    }

    /**
     * Get device from user agent string
     * @param $userAgent
     * @return string
     */
    protected function getDevice($userAgent)
    {
        $this->agent->setUserAgent($userAgent);
        $device = 'Other';

        if ( $this->agent->isTablet() )
            $device = 'Tablet';
        elseif ( $this->agent->isPhone() )
            $device = 'Phone';
        elseif ( $this->agent->isDesktop() )
            $device = 'Desktop';

        return $device;
    }

    /**
     * Get platform + its version from user agent string
     * @param $userAgent
     * @return string
     */
    protected function getPlatform($userAgent)
    {
        $this->agent->setUserAgent($userAgent);
        $platform = $this->agent->platform();
        $version = $this->agent->version($platform);

        return "$platform $version";
    }

    /**
     * Get browser from user agent string
     * @param $userAgent
     * @return string
     */
    protected function getBrowser($userAgent)
    {
        $this->agent->setUserAgent($userAgent);
        return $this->agent->browser();
    }
}