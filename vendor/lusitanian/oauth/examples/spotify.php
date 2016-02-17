<?php

/**
 * Example of retrieving an authentication token of the Spotify service
 *
 * PHP version 5.4
 *
 * @author     Craig Morris <craig.michael.morris@gmail.com>
 * @author     Ben King <ben.kingsy@gmail.com>
 * @copyright  Copyright (c) 2012 The authors
 * @license    http://www.opensource.org/licenses/mit-license.html  MIT License
 */

use OAuth\OAuth2\Service\Spotify;
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
    $servicesCredentials['spotify']['key'],
    $servicesCredentials['spotify']['secret'],
    $currentUri->getAbsoluteUri()
);

// Instantiate the Spotify service using the credentials, http client and storage mechanism for the token
/** @var $spotifyService Spotify */
$spotifyService = $serviceFactory->createService('spotify', $credentials, $storage);

if (!empty($_GET['code'])) {
    // This was a callback request from Spotify, get the token
    $spotifyService->requestAccessToken($_GET['code']);

    // Send a request with it
    $result = json_decode($spotifyService->request('me'), true);

    // Show some of the resultant data
    echo 'Your unique user id is: ' . $result['id'] . ' and your name is ' . $result['display_name'];

} elseif (!empty($_GET['go']) && $_GET['go'] === 'go') {
    $url = $spotifyService->getAuthorizationUri();
    header('Location: ' . $url);
} else {
    $url = $currentUri->getRelativeUri() . '?go=go';
    echo "<a href='$url'>Login with Spotify!</a>";
}
