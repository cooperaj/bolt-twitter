<?php

namespace Bolt\Extension\Cooperaj\Twitter\Twig;

use Silex\Application;
use Bolt\Extension\Cooperaj\Twitter\TwitterExtension as Extension;
use Bolt\Extension\Cooperaj\Twitter\Service\TwitterService;

class TwitterExtension
{
    const TWITTER_URI = 'https://twitter.com';

    /**
     * @var Application
     */
    private $container;

    /**
     * @var array
     */
    private $config;

    /**
     * @param Application $container
     * @param $config
     * @internal param Pimple $app
     */
    public function __construct(Application $container, $config)
    {
        $this->container = $container;
        $this->config = $config;
    }

    /**
     * Renders a tweet timeline using the configured details
     *
     * @param string $listing
     * @return \Twig_Markup
     */
    public function twigTimelineDisplay($listing = 'default')
    {
        $listing_config = $this->config['listings'][$listing];

        $user = null;
        if (isset($listing_config['user'])) {
            $user = $listing_config['user'];
        }

        /** @var TwitterService $twitter */
        $twitter = $this->container[Extension::CONTAINER . '.service'];

        $tweets = $twitter->getUserTimeline($user, $listing_config['tweets_to_show']);

        if (isset($tweets->errors) && !is_null($tweets->errors)) {
            return $this->errorEncountered($tweets->errors);
        }

        $timeline_html = '';
        foreach ($tweets as $tweet) {
            // retweeted tweets have all the important content in the child element 'retweeted_status'
            // in order to work with retweets we map them to the top level tweet and add a new field
            // so we can work out who did the retweeting.
            if (isset($tweet->retweeted_status)) {
                $retweeted_by = $tweet->user;
                $tweet = $tweet->retweeted_status;
                $tweet->retweeted_by = $retweeted_by;
            }

            $timeline_html .= $this->container['render']->render('twitter_tweet.twig', array('tweet' => $tweet));
        }

        return new \Twig_Markup($timeline_html, 'UTF-8');
    }

    /**
     * Filter function to parse tweet text into tweet text with entity links.
     *
     * @param \stdClass $tweet A Twitter API tweet object
     * @return \Twig_Markup The correct tweet text for display.
     */
    public function twigAddTweetEntityLinks($tweet)
    {
        return new \Twig_Markup(trim($this->addTweetEntityLinks($tweet)), 'UTF-8');
    }

    /**
     * @param \stdClass $user A Twitter API user object
     * @return \Twig_Markup
     */
    public function twigLinkUser($user)
    {
        return new \Twig_Markup(trim($this->linkUser($user)), 'UTF-8');
    }

    /**
     * @param \stdClass $tweet A Twitter API tweet object
     * @return \Twig_Markup
     */
    public function twigLinkTweet($tweet)
    {
        return new \Twig_Markup(trim($this->linkTweet($tweet)), 'UTF-8');
    }

    /**
     * @param $errors
     * @return \Twig_Markup
     */
    protected function errorEncountered($errors)
    {
        $error_html = $this->container['render']->render('twitter_error.twig', array('errors' => $errors));

        return new \Twig_Markup($error_html, 'UTF-8');
    }

    /**
     * Parses a twitter API tweet object and generate the correctly linked text for display.
     *
     * http://stackoverflow.com/a/15390225
     *
     * @param \stdClass $tweet A Twitter API tweet object
     * @return string
     */
    protected function addTweetEntityLinks($tweet)
    {
        $text = $tweet->text;
        $entities = isset($tweet->entities) ? $tweet->entities : array();

        $replacements = array();
        if (isset($entities->hashtags)) {
            foreach ($entities->hashtags as $hashtag) {
                list ($start, $end) = $hashtag->indices;
                $replacements[$start] = array(
                    $start,
                    $end,
                    sprintf(
                        '<a href="%s">#%s</a>',
                        [
                            self::TWITTER_URI . '/search?q=' . urlencode($hashtag->text),
                            htmlentities($hashtag->text)
                        ]
                    )
                );
            }
        }
        if (isset($entities->urls)) {
            foreach ($entities->urls as $url) {
                list ($start, $end) = $url->indices;
                // you can also use $url->expanded_url in place of $url->url
                $replacements[$start] = array(
                    $start,
                    $end,
                    sprintf(
                        '<a href="%s">%s</a>',
                        [
                            $url->url,
                            htmlentities($url->display_url)
                        ]
                    )
                );
            }
        }
        if (isset($entities->user_mentions)) {
            foreach ($entities->user_mentions as $mention) {
                list ($start, $end) = $mention->indices;
                $replacements[$start] = array(
                    $start,
                    $end,
                    sprintf(
                        '<a href="%s">@%s</a>',
                        [
                            self::TWITTER_URI . '/' . $mention->screen_name,
                            htmlentities($mention->screen_name)
                        ]
                    )
                );
            }
        }
        if (isset($entities->media)) {
            foreach ($entities->media as $media) {
                list ($start, $end) = $media->indices;
                $replacements[$start] = array(
                    $start,
                    $end,
                    sprintf(
                        '<a href="%s">%s</a>',
                        [
                            $media->url,
                            htmlentities($media->display_url)
                        ]
                    )
                );
            }
        }

        // sort in reverse order by start location
        krsort($replacements);

        foreach ($replacements as $replace_data) {
            list ($start, $end, $replace_text) = $replace_data;
            $text = mb_substr($text, 0, $start, 'UTF-8') . $replace_text . mb_substr($text, $end, null, 'UTF-8');
        }

        return $text;
    }

    /**
     * Turns a twitter API user into a url for that user.
     *
     * @param \stdClass $user A Twitter API user object
     * @return string
     */
    protected function linkUser($user)
    {
        return self::TWITTER_URI . '/' . urlencode($user->screen_name);
    }

    /**
     * Turns a twitter API tweet into a url for that tweet.
     *
     * @param \stdClass $tweet A Twitter API tweet object
     * @return string
     */
    protected function linkTweet($tweet)
    {
        return $this->linkUser($tweet->user) . '/status/' . $tweet->id_str;
    }

    /**
     * @param \stdClass $tweet A Twitter API tweet object
     * @return string
     */
    protected function linkRetweet($tweet)
    {
        return self::TWITTER_URI . '/intent/retweet?tweet_id=' . $tweet->id_str;
    }

    /**
     * @param \stdClass $tweet A Twitter API tweet object
     * @return string
     */
    protected function linkAddTweetToFavorites($tweet)
    {
        return self::TWITTER_URI . '/intent/favorite?tweet_id=' . $tweet->id_str;
    }
}
