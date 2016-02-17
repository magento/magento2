<?php

/**
 * This file sets up the information needed to test the examples in different environments.
 *
 * PHP version 5.4
 *
 * @author     David Desberg <david@daviddesberg.com>
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 * @copyright  Copyright (c) 2012 The authors
 * @license    http://www.opensource.org/licenses/mit-license.html  MIT License
 */

/**
 * @var array A list of all the credentials to be used by the different services in the examples
 */
$servicesCredentials = array(
    'amazon' => array(
        'key'       => '',
        'secret'    => '',
    ),
    'bitbucket' => array(
        'key'       => '',
        'secret'    => '',
    ),
    'bitly' => array(
        'key'       => '',
        'secret'    => '',
    ),
    'bitrix24' => array(
        'key'       => '',
        'secret'    => '',
    ),
    'box' => array(
        'key'       => '',
        'secret'    => '',
    ),
    'buffer' => array(
        'key'       => '',
        'secret'    => '',
    ),
    'dailymotion' => array(
        'key'       => '',
        'secret'    => '',
    ),
    'deviantart' => array(
        'key'       => '',
        'secret'    => '',
    ),
    'dropbox' => array(
        'key'       => '',
        'secret'    => '',
    ),
    'etsy' => array(
        'key'       => '',
        'secret'    => '',
    ),
    'eveonline' => array(
        'key' => '',
        'secret' => '',
    ),
    'facebook' => array(
        'key'       => '',
        'secret'    => '',
    ),
    'fitbit' => array(
        'key'       => '',
        'secret'    => '',
    ),
    'fivehundredpx' => array(
        'key'       => '',
        'secret'    => '',
    ),
    'flickr' => array(
        'key'       => '',
        'secret'    => '',
    ),
    'foursquare' => array(
        'key'       => '',
        'secret'    => '',
    ),
    'github' => array(
        'key'       => '',
        'secret'    => '',
    ),
    'google' => array(
        'key'       => '',
        'secret'    => '',
    ),
    'hubic' => array(
        'key'       => '',
        'secret'    => '',
    ),
    'instagram' => array(
        'key'       => '',
        'secret'    => '',
    ),
    'linkedin' => array(
        'key'       => '',
        'secret'    => '',
    ),
    'mailchimp' => array(
        'key'       => '',
        'secret'    => '',
    ),
    'microsoft' => array(
        'key'       => '',
        'secret'    => '',
    ),
    'nest' => array(
        'key'       => '',
        'secret'    => '',
    ),
    'netatmo' => array(
        'key'       => '',
        'secret'    => '',
    ),
    'parrotFlowerPower' => array(
        'key'       => '',
        'secret'    => '',
    ),
    'paypal' => array(
        'key'       => '',
        'secret'    => '',
    ),
    'pinterest' => array(
        'key'       => '',
        'secret'    => '',
    ),
    'pocket' => array(
        'key'       => '',
    ),
    'quickbooks' => array(
        'key'       => '',
        'secret'    => '',
    ),
    'reddit' => array(
        'key'       => '',
        'secret'    => '',
    ),
    'redmine' => array(
        'key'       => '',
        'secret'    => ''
    ),
    'runkeeper' => array(
        'key'       => '',
        'secret'    => '',
    ),
    'scoopit' => array(
        'key'       => '',
        'secret'    => ''
    ),
    'soundcloud' => array(
        'key'       => '',
        'secret'    => '',
    ),
    'spotify' => array(
        'key'       => '',
        'secret'    => '',
    ),
    'strava' => array(
        'key'       => '',
        'secret'    => '',
    ),
    'tumblr' => array(
        'key'       => '',
        'secret'    => '',
    ),
    'twitter' => array(
        'key'       => '',
        'secret'    => '',
    ),
    'ustream' => array(
        'key'       => '',
        'secret'    => '',
    ),
    'vimeo' => array(
        'key'       => '',
        'secret'    => '',
    ),
    'yahoo' => array(
        'key'       => '',
        'secret'    => ''
    ),
    'yammer' => array(
        'key'       => '',
        'secret'    => ''
    ),
);

/** @var $serviceFactory \OAuth\ServiceFactory An OAuth service factory. */
$serviceFactory = new \OAuth\ServiceFactory();
