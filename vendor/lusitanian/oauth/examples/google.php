<?php

/**
 * Example of retrieving an authentication token of the Google service
 *
 * PHP version 5.4
 *
 * @author     David Desberg <david@daviddesberg.com>
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 * @copyright  Copyright (c) 2012 The authors
 * @license    http://www.opensource.org/licenses/mit-license.html  MIT License
 */

use OAuth\OAuth2\Service\Google;
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
    $servicesCredentials['google']['key'],
    $servicesCredentials['google']['secret'],
    $currentUri->getAbsoluteUri()
);

// Instantiate the Google service using the credentials, http client and storage mechanism for the token
/** @var $googleService Google */
$googleService = $serviceFactory->createService('google', $credentials, $storage, array('userinfo_email', 'userinfo_profile'));

if (!empty($_GET['code'])) {
    // retrieve the CSRF state parameter
    $state = isset($_GET['state']) ? $_GET['state'] : null;

    // This was a callback request from google, get the token
    $googleService->requestAccessToken($_GET['code'], $state);

    // Send a request with it
    $result = json_decode($googleService->request('userinfo'), true);

    // Show some of the resultant data
    echo 'Your unique google user id is: ' . $result['id'] . ' and your name is ' . $result['name'];

} elseif (!empty($_GET['go']) && $_GET['go'] === 'go') {
    $url = $googleService->getAuthorizationUri();
    header('Location: ' . $url);
} else {
    $url = $currentUri->getRelativeUri() . '?go=go';
    echo "<a href='$url'>Login with Google!</a>";
}
