<?php

namespace Bolt\Extension\Cooperaj\BoltTwitter;

use Endroid\Twitter\Twitter as ETwitter;

class Twitter {

    protected $twitter_service;

    /**
     * Class constructor
     *
     * @param $consumer_key
     * @param $consumer_secret
     * @param $access_token
     * @param $access_token_secret
     * @param null $apiUrl
     */
    public function __construct($consumer_key, $consumer_secret, $access_token, $access_token_secret, $apiUrl = null)
    {
        $this->twitter_service = new ETwitter(
            $consumer_key,
            $consumer_secret,
            $access_token,
            $access_token_secret,
            $apiUrl = null
        );
    }

    public function getAccountTimeline($number_to_show)
    {
        return $this->twitter_service->getTimeline(array());
    }

    public function getUserTimeline($user, $number_to_show)
    {

    }

}
