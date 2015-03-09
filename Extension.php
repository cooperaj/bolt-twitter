<?php

namespace Bolt\Extension\Cooperaj\BoltTwitter;

use Bolt\BaseExtension;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

class Extension extends BaseExtension
{
    /**
     * Extension name
     *
     * @var string
     */
    const NAME = "BoltTwitter";

    /**
     * Extension's service container
     *
     * @var string
     */
    const CONTAINER = 'extensions.BoltTwitter';

    public function getName()
    {
        return Extension::NAME;
    }

    public function initialize()
    {
        $this->createServiceLayer();

        /*
         * Frontend
         */
        if ($this->app['config']->getWhichEnd() == 'frontend') {
            // TODO Implement some sort of nice styling for the output
            //$this->addCss('assets/css/bolt-twitter.css');

            // Twig functions
            $this->app['twig']->addExtension(new Twig\BoltTwitterExtension($this->app));

            // Add twig templates
            $this->app['twig.loader.filesystem']->prependPath(__DIR__ . "/assets/twig");


        }
    }

    /**
     * Adds a service definition to the application container so that we can retrieve an
     * instance of the service from elsewhere within the code.
     */
    protected function createServiceLayer()
    {
        $this->app[Extension::CONTAINER . '.service'] = function ($c) {
            $consumer_key = $c[Extension::CONTAINER]->config['consumer_key'];
            $consumer_secret = $c[Extension::CONTAINER]->config['consumer_secret'];
            $access_token = $c[Extension::CONTAINER]->config['access_token'];
            $access_token_secret = $c[Extension::CONTAINER]->config['access_token_secret'];

            if ($consumer_key === '' || is_null($consumer_key) ||
                $consumer_secret === '' || is_null($consumer_secret) ||
                $access_token === '' || is_null($access_token) ||
                $access_token_secret === '' || is_null($access_token_secret)
            ) {
                throw new InvalidConfigurationException(
                    'Necessary Twitter API key/token values not specified or are incorrect.'
                );
            }

            return new Twitter($c, $consumer_key, $consumer_secret, $access_token, $access_token_secret, $apiUrl = null);
        };
    }

    /**
     * Set the defaults for configuration parameters
     *
     * @return array
     */
    protected function getDefaultConfig()
    {
        return array(
            'consumer_key' => '',
            'consumer_secret' => '',
            'access_token' => '',
            'access_token_secret' => '',
            'listings' => array(
                'default' => array(
                    'tweets_to_show' => 5
                )
            )
        );
    }
}






