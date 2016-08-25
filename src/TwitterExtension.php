<?php

namespace Bolt\Extension\Cooperaj\Twitter;

use Bolt\Extension\SimpleExtension;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

class TwitterExtension extends SimpleExtension
{
    /**
     * Extension's service container
     *
     * @var string
     */
    const CONTAINER = 'extensions.Twitter';

    public function initialize()
    {
        $app = $this->getContainer();
        $this->createServiceLayer();

        $app['twig'] = $app->share(
          $app->extend(
            'twig',
            function ($twig) use ($app) {
                $twig->addExtension(new Twig\TwitterExtension($app, $this->getConfig()));
                
                return $twig;
            }
          )
        );

        $app['twig.loader.bolt_filesystem']->addDir(__DIR__ . "/assets/twig");
    }

    /**
     * Adds a service definition to the application container so that we can retrieve an
     * instance of the service from elsewhere within the code.
     */
    protected function createServiceLayer()
    {
        $app = $this->getContainer();
        $config = $this->getConfig();

        $app[self::CONTAINER . '.service'] = function ($c) use ($config) {
            $consumer_key = $config['consumer_key'];
            $consumer_secret = $config['consumer_secret'];
            $access_token = $config['access_token'];
            $access_token_secret = $config['access_token_secret'];

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






