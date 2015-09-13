<?php namespace CaffeineAddicts\MailAwesome;

use GuzzleHttp\Psr7\Response;

class MailAwesomeResponse {

    protected $mailAwesomeObject;

    protected $guzzleResponse;

    public function wasSent()
    {
        if ($this->guzzleResponse->getStatusCode() == 200)
        {
            return true;
        }

        return false;
    }

    public function __construct(MailAwesome $mailAwesomeObject, Response $guzzleResponse)
    {
        $this->mailAwesomeObject = $mailAwesomeObject;
        $this->guzzleResponse = $guzzleResponse;
    }

}