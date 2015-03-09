<?php

namespace Bolt\Extension\Cooperaj\BoltTwitter\Twig;

use Bolt\Application;
use Bolt\Extension\Cooperaj\BoltTwitter\Extension;
use Bolt\Extension\Cooperaj\BoltTwitter\Twitter;

class BoltTwitterExtension extends \Twig_Extension
{
    /**
     * @var Application
     */
    private $app;

    /**
     * @var array
     */
    private $config;

    /**
     * @var \Twig_Environment
     */
    private $twig = null;

    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->config = $this->app[Extension::CONTAINER]->config;
    }

    public function initRuntime(\Twig_Environment $environment)
    {
        $this->twig = $environment;
    }

    /**
     * Return the name of the extension
     */
    public function getName()
    {
        return 'bolttwitter.extension';
    }

    /**
     * The functions we add
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('twitter_timeline', array($this, 'twigTimelineDisplay'))
        );
    }

    public function twigTimelineDisplay($listing = 'default')
    {
        $listing_config = $this->config['listings'][$listing];

        /** @var Twitter $twitter */
        $twitter = $this->app[Extension::CONTAINER . '.service'];

        $tweets = $twitter->getAccountTimeline($listing_config['tweets_to_show']);

        if (isset($tweets->errors) &&  ! is_null($tweets->errors))
            return $this->errorEncountered($tweets->errors);

            $timeline_html .= $this->app['render']->render('bolttwitter_tweet.twig', array('tweet' => $tweet));

        return new \Twig_Markup($timeline_html, 'UTF-8');
    }

    protected function errorEncountered($errors)
    {
        $error_html = $this->app['render']->render('bolttwitter_error.twig', array('errors' => $errors));

        return new \Twig_Markup($error_html, 'UTF-8');
    }
}
