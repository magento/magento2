<?php

/**
 * Example of retrieving an authentication token of the harvest service
 *
 * PHP version 5.4
 *
 * @author     David Desberg <david@daviddesberg.com>
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 * @copyright  Copyright (c) 2012 The authors
 * @license    http://www.opensource.org/licenses/mit-license.html  MIT License
 */

use OAuth\Common\Consumer\Credentials;
use OAuth\Common\Storage\Session;
use OAuth\Common\Token\Exception\ExpiredTokenException;
use OAuth\OAuth2\Service\Harvest;

/**
 * Bootstrap the example
 */
require_once __DIR__ . '/bootstrap.php';

$serviceName = 'Harvest';
$scopes = array();

// Session storage
$storage = new Session();

// Setup the credentials for the requests
$credentials = new Credentials(
    $servicesCredentials['harvest']['key'],
    $servicesCredentials['harvest']['secret'],
    $currentUri->getAbsoluteUri()
);

// Instantiate the Harvest service using the credentials, http client and storage mechanism for the token
/** @var $harves Harves */
$harvest = $serviceFactory->createService($serviceName, $credentials, $storage, $scopes);

if (!empty($_GET['clearToken'])) {
    // Clear the current AccessToken and go back to the Beginning.
    $storage->clearToken($serviceName);
    header('Location: ' . $currentUri->getAbsoluteUri());

} elseif ($storage->hasAccessToken($serviceName)) {
    // fetch the accessToken for the service
    $accessToken = $storage->retrieveAccessToken($serviceName);

    // is the accessToken expired? then let's refesh it!
    if ($accessToken->isExpired() === TRUE) {
        $harvest->refreshAccessToken($accessToken);
    }

    // use the service with the valid access token to fetch my email
    $result = json_decode($harvest->request('account/who_am_i'), true);
    echo 'The email on your harvest account is ' . $result['user']['email'];

    $url = $currentUri->getRelativeUri() . '?clearToken=1';
    echo " <a href='$url'>Click here to clear the current access token</a>";

} elseif (!empty($_GET['code'])) {
    // This was a callback request from harvest, get the token
    $harvest->requestAccessToken($_GET['code']);
    header('Location: ' . $currentUri->getAbsoluteUri());

} elseif (!empty($_GET['go']) && $_GET['go'] === 'go') {
    // Redirect to the Authorization uri
    $url = $harvest->getAuthorizationUri();
    header('Location: ' . $url);
} else {
    $url = $currentUri->getRelativeUri() . '?go=go';
    echo "<a href='$url'>Login with Harvest!</a>";
}
