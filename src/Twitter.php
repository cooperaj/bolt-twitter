<?php

namespace Bolt\Extension\Cooperaj\Twitter;

use Bolt\Application;
use Buzz\Exception\RequestException;
use Doctrine\Common\Cache\Cache;
use Endroid\Twitter\Twitter as ETwitter;

class Twitter
{
    /** @var Cache */
    private $cache;

    private $twitter_service;

    /**
     * Class constructor
     *
     * @param Application $app
     * @param $consumer_key
     * @param $consumer_secret
     * @param $access_token
     * @param $access_token_secret
     * @param null $apiUrl
     */
    public function __construct(
        Application $app,
        $consumer_key,
        $consumer_secret,
        $access_token,
        $access_token_secret,
        $apiUrl = null
    ) {
        $this->cache = new ResilientCache($app['cache']);

        $this->twitter_service = new ETwitter(
            $consumer_key,
            $consumer_secret,
            $access_token,
            $access_token_secret,
            $apiUrl = null
        );
    }

    public function getUserTimeline($user = null, $number_to_show)
    {
        $options = array('count' => $number_to_show);
        if (!is_null($user)) {
            $options['screen_name'] = $user;
        }

        $twitter_service = $this->twitter_service;
        $tweets = $this->cache->fetch($user . $number_to_show, function() use ($twitter_service, $options) {
            return $twitter_service->getTimeline($options);
        });

        if ($tweets) {
            $this->cache->save($user . $number_to_show, $tweets, 120);
        }

        return $tweets;
    }
}
