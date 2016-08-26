<?php

namespace Bolt\Extension\Cooperaj\Twitter\Provider;

use Bolt\Extension\Cooperaj\Twitter\Twitter;
use Bolt\Extension\Cooperaj\Twitter\TwitterExtension;
use Silex\Application;
use Silex\ServiceProviderInterface;
use Bolt\Extension\Cooperaj\Twitter\Twig;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

class TwitterProvider implements ServiceProviderInterface
{
    /**
     * Registers services on the given app.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     */
    public function register(Application $app)
    {
        $app['extensions.twitter.service'] = $app->share(
            function ($app) {
                /** @var TwitterExtension $extension */
                $extension = $app['extensions']->get('Cooperaj/Twitter');

                $config = $extension->getConfig();

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

                return new Twitter($app, $consumer_key, $consumer_secret, $access_token, $access_token_secret, $apiUrl = null);
            }
        );

        $app['extensions.twitter.twig'] = $app->share(
            function ($app) {
                /** @var TwitterExtension $extension */
                $extension = $app['extensions']->get('Cooperaj/Twitter');

                $twig = new Twig\TwitterExtension($app, $extension->getConfig());

                return $twig;
            }
        );
    }

    /**
     * Bootstraps the application.
     *
     * This method is called after all services are registered
     * and should be used for "dynamic" configuration (whenever
     * a service must be requested).
     */
    public function boot(Application $app)
    {
        // TODO: Implement boot() method.
    }
}