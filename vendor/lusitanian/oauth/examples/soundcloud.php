<?php

/**
 * Example of retrieving an authentication token of the SoundCloud service
 *
 * PHP version 5.4
 *
 * @author     David Desberg <david@daviddesberg.com>
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 * @copyright  Copyright (c) 2012 The authors
 * @license    http://www.opensource.org/licenses/mit-license.html  MIT License
 */

use OAuth\OAuth2\Service\SoundCloud;
use OAuth\Common\Storage\Session;
use OAuth\Common\Consumer\Credentials;

/**
 * Bootstrap the example
 */
require_once __DIR__ . '/bootstrap.php';

// Session storage
$storage = new Session();

// Setup the credentials for the requests
$credentials = new Credentials(
    $servicesCredentials['soundcloud']['key'],
    $servicesCredentials['soundcloud']['secret'],
    $currentUri->getAbsoluteUri()
);

// Instantiate the SoundCloud service using the credentials, http client and storage mechanism for the token
/** @var $soundcloudService SoundCloud */
$soundcloudService = $serviceFactory->createService('soundCloud', $credentials, $storage);

if (!empty($_GET['code'])) {
    // This was a callback request from SoundCloud, get the token
    $soundcloudService->requestAccessToken($_GET['code']);

    // Send a request with it
    $result = json_decode($soundcloudService->request('me.json'), true);

    // Show some of the resultant data
    echo 'Your unique user id is: ' . $result['id'] . ' and your name is ' . $result['username'];

} elseif (!empty($_GET['go']) && $_GET['go'] === 'go') {
    $url = $soundcloudService->getAuthorizationUri();
    header('Location: ' . $url);
} else {
    $url = $currentUri->getRelativeUri() . '?go=go';
    echo "<a href='$url'>Login with SoundCloud!</a>";
}
