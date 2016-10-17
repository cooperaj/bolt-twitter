<?php

namespace Bolt\Extension\Cooperaj\Twitter;

use Bolt\Extension\SimpleExtension;

class TwitterExtension extends SimpleExtension
{
    /**
     * Extension's service container
     *
     * @var string
     */
    const CONTAINER = 'extensions.twitter';

    /**
     * {@inheritdoc}
     */
    public function getServiceProviders()
    {
        return [
            $this,
            new Provider\TwitterProvider()
        ];
    }

    protected function registerTwigPaths()
    {
        return [
            "templates"
        ];
    }

    protected function registerTwigFunctions()
    {
        $app = $this->getContainer();

        return [
            'twitter_timeline' => [[$app['extensions.twitter.twig'], 'twigTimelineDisplay'], []]
        ];
    }

    protected function registerTwigFilters()
    {
        $app = $this->getContainer();

        return [
            'tweet_entityfy' => [[$app['extensions.twitter.twig'], 'twigAddTweetEntityLinks'], []],
            'tweet_user_link' => [[$app['extensions.twitter.twig'], 'twigLinkUser'], []],
            'tweet_status_link' => [[$app['extensions.twitter.twig'], 'twigLinkTweet'], []]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        return parent::getConfig();
    }

    /**
     * Set the defaults for configuration parameters
     *
     * @return array
     */
    protected function getDefaultConfig()
    {
        return [
            'consumer_key' => '',
            'consumer_secret' => '',
            'access_token' => '',
            'access_token_secret' => '',
            'listings' => [
                'default' => [
                    'tweets_to_show' => 5
                ]
            ]
        ];
    }
}






