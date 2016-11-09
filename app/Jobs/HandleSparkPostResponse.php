<?php

namespace App\Jobs;

use App\AvaHelper\EmailEventsHandler;
use App\SparkyResponse;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class HandleSparkPostResponse implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $sparkyResponse;

    /**
     * Create a new job instance.
     * @param SparkyResponse $sparkyResponse
     */
    public function __construct(SparkyResponse $sparkyResponse)
    {
        $this->sparkyResponse = $sparkyResponse;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ( $this->sparkyResponse ) {
            $request  = json_decode($this->sparkyResponse->body);

            if ( count($request) ) {
                $emailEventsHandler = new EmailEventsHandler($request);
                $emailEventsHandler->handleEvents();
            }
        }
    }
}
