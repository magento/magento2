PHPoAuthLib
===========
**NOTE: I'm looking for someone who could help to maintain this package alongside me, just because I don't have a ton of time to devote to it. However, I'm still going to keep trying to pay attention to PRs, etc.**

PHPoAuthLib provides oAuth support in PHP 5.3+ and is very easy to integrate with any project which requires an oAuth client.

[![Build Status](https://travis-ci.org/Lusitanian/PHPoAuthLib.png?branch=master)](https://travis-ci.org/Lusitanian/PHPoAuthLib)
[![Code Coverage](https://scrutinizer-ci.com/g/Lusitanian/PHPoAuthLib/badges/coverage.png?s=a0a15bebfda49e79f9ce289b00c6dfebd18fc98e)](https://scrutinizer-ci.com/g/Lusitanian/PHPoAuthLib/)
[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/Lusitanian/PHPoAuthLib/badges/quality-score.png?s=c5976d2fefceb501f0d886c1a5bf087e69b44533)](https://scrutinizer-ci.com/g/Lusitanian/PHPoAuthLib/)
[![Latest Stable Version](https://poser.pugx.org/lusitanian/oauth/v/stable.png)](https://packagist.org/packages/lusitanian/oauth)
[![Total Downloads](https://poser.pugx.org/lusitanian/oauth/downloads.png)](https://packagist.org/packages/lusitanian/oauth)

Installation
------------
This library can be found on [Packagist](https://packagist.org/packages/lusitanian/oauth).
The recommended way to install this is through [composer](http://getcomposer.org).

Edit your `composer.json` and add:

```json
{
    "require": {
        "lusitanian/oauth": "~0.3"
    }
}
```

And install dependencies:

```bash
$ curl -sS https://getcomposer.org/installer | php
$ php composer.phar install
```

Features
--------
- PSR-0 compliant for easy interoperability
- Fully extensible in every facet.
    - You can implement any service with any custom requirements by extending the protocol version's `AbstractService` implementation.
    - You can use any HTTP client you desire, just create a class utilizing it which implements `OAuth\Common\Http\ClientInterface` (two implementations are included)
    - You can use any storage mechanism for tokens. By default, session, in-memory and Redis.io (requires PHPRedis) storage mechanisms are included. Implement additional mechanisms by implementing `OAuth\Common\Token\TokenStorageInterface`.

Service support
---------------
The library supports both oAuth 1.x and oAuth 2.0 compliant services. A list of currently implemented services can be found below.

Included service implementations
--------------------------------
- OAuth1
    - 500px
    - BitBucket
    - Etsy
    - FitBit
    - Flickr
    - QuickBooks
    - Scoop.it!
    - Tumblr
    - Twitter
    - Xing
    - Yahoo
- OAuth2
    - Amazon
    - BitLy
    - Bitrix24
    - Box
    - Dailymotion
    - DeviantArt
    - Dropbox
    - Eve Online
    - Facebook
    - Foursquare
    - GitHub
    - Google
    - Harvest
    - Heroku
    - Hubic
    - Instagram
    - Jawbone UP
    - LinkedIn
    - Mailchimp
    - Microsoft
    - Nest
    - Netatmo
    - Parrot Flower Power
    - PayPal
    - Pinterest
    - Pocket
    - Reddit
    - RunKeeper
    - SoundCloud
    - Spotify
    - Strava
    - Ustream
    - Vimeo
    - Vkontakte
    - Yammer
- more to come!

Examples
--------
Examples of basic usage are located in the examples/ directory.

Usage
------
For usage with complete auth flow, please see the examples. More in-depth documentation will come with release 1.0.

Framework Integration
---------------------
* Lithium: Sébastien Charrier has written [an adapter](https://github.com/scharrier/li3_socialauth) for the library.
* Laravel 4: Dariusz Prząda has written [a service provider](https://github.com/artdarek/oauth-4-laravel) for the library.

Extensions
----------
* Extract normalized user data from OAuth Services with the library [PHPoAuthUserData](https://github.com/Oryzone/PHPoAuthUserData) by Luciano Mammino

Tests
------
To run the tests, you must install dependencies with `composer install --dev`
