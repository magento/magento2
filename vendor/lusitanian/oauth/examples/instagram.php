<?php

/**
 * Example of retrieving an authentication token of the Instagram service
 *
 * PHP version 5.4
 *
 * @author     David Desberg <david@daviddesberg.com>
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 * @author     Hannes Van De Vreken <vandevreken.hannes@gmail.com>
 * @copyright  Copyright (c) 2012 The authors
 * @license    http://www.opensource.org/licenses/mit-license.html  MIT License
 */

use OAuth\OAuth2\Service\Instagram;
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
    $servicesCredentials['instagram']['key'],
    $servicesCredentials['instagram']['secret'],
    $currentUri->getAbsoluteUri()
);

$scopes = array('basic', 'comments', 'relationships', 'likes');

// Instantiate the Instagram service using the credentials, http client and storage mechanism for the token
/** @var $instagramService Instagram */
$instagramService = $serviceFactory->createService('instagram', $credentials, $storage, $scopes);

if (!empty($_GET['code'])) {
    // retrieve the CSRF state parameter
    $state = isset($_GET['state']) ? $_GET['state'] : null;

    // This was a callback request from Instagram, get the token
    $instagramService->requestAccessToken($_GET['code'], $state);

    // Send a request with it
    $result = json_decode($instagramService->request('users/self'), true);

    // Show some of the resultant data
    echo 'Your unique instagram user id is: ' . $result['data']['id'] . ' and your name is ' . $result['data']['full_name'];

} elseif (!empty($_GET['go']) && $_GET['go'] === 'go') {
    $url = $instagramService->getAuthorizationUri();
    header('Location: ' . $url);
} else {
    $url = $currentUri->getRelativeUri() . '?go=go';
    echo "<a href='$url'>Login with Instagram!</a>";
}
