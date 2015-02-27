<?php

namespace Bolt\Extension\Cooperaj\BoltTwitter;

use Bolt\Application;
use Bolt\BaseExtension;

use Endroid\Twitter\Twitter;

class Extension extends BaseExtension
{
	public function __construct(Application $app) {
		$twitter = new Twitter("", "", "", "");
	}

    public function initialize() {
        $this->addCss('assets/css/bolt-twitter.css');
    }

    public function getName()
    {
        return "bolttwitter";
    }

}






