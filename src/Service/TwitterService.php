<?php

namespace Bolt\Extension\Cooperaj\Twitter\Service;

use Bolt\Extension\Cooperaj\Twitter\Cache\ResilienceCache;
use Endroid\Twitter\Twitter as ETwitter;
use Silex\Application;

class TwitterService
{
    /**
     * @var ResilienceCache
     */
    private $cache;

    /**
     * @var ETwitter
     */
    private $lowLevelService;

    /**
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
        $this->cache = new ResilienceCache($app['cache']);
        $this->lowLevelService = new ETwitter(
            $consumer_key,
            $consumer_secret,
            $access_token,
            $access_token_secret,
            $apiUrl = null
        );
    }

    /**
     * @param null $user
     * @param $number_to_show
     * @return mixed
     */
    public function getUserTimeline($user = null, $number_to_show)
    {
        $options = array('count' => $number_to_show);

        if (!is_null($user)) {
            $options['screen_name'] = $user;
        }

        $twitter_service = $this->lowLevelService;

        $tweets = $this->cache->fetch(
            $user . $number_to_show,
            function () use ($twitter_service, $options) {
                return $twitter_service->getTimeline($options);
            }
        );

        if ($tweets) {
            $this->cache->save($user . $number_to_show, $tweets, 120);
        }

        return $tweets;
    }
}
