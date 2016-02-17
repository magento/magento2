<?php

/**
 * Example of retrieving an authentication token from the BitBucket service
 *
 * PHP version 5.4
 * @author     Ændrew Rininsland <me@aendrew.com>
 * 
 * Shamelessly cribbed from work by:
 * @author     David Desberg <david@daviddesberg.com>
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 * @copyright  Copyright (c) 2012 The authors
 * @license    http://www.opensource.org/licenses/mit-license.html  MIT License
 */

use OAuth\OAuth1\Service\BitBucket;
use OAuth\Common\Storage\Session;
use OAuth\Common\Consumer\Credentials;

/**
 * Bootstrap the example
 */
require_once __DIR__ . '/bootstrap.php';

// We need to use a persistent storage to save the token, because oauth1 requires the token secret received before'
// the redirect (request token request) in the access token request.
$storage = new Session();

// Setup the credentials for the requests
$credentials = new Credentials(
    $servicesCredentials['bitbucket']['key'],
    $servicesCredentials['bitbucket']['secret'],
    $currentUri->getAbsoluteUri()
);

// Instantiate the BitBucket service using the credentials, http client and storage mechanism for the token
/** @var $bbService BitBucket */
$bbService = $serviceFactory->createService('BitBucket', $credentials, $storage);

if (!empty($_GET['oauth_token'])) {
    $token = $storage->retrieveAccessToken('BitBucket');

    // This was a callback request from BitBucket, get the token
    $bbService->requestAccessToken(
        $_GET['oauth_token'],
        $_GET['oauth_verifier'],
        $token->getRequestTokenSecret()
    );

    // Send a request now that we have access token
    $result = json_decode($bbService->request('user/repositories'));

    echo('The first repo in the list is ' . $result[0]->name);

} elseif (!empty($_GET['go']) && $_GET['go'] === 'go') {
    // extra request needed for oauth1 to request a request token :-)
    $token = $bbService->requestRequestToken();

    $url = $bbService->getAuthorizationUri(array('oauth_token' => $token->getRequestToken()));
    header('Location: ' . $url);
} else {
    $url = $currentUri->getRelativeUri() . '?go=go';
    echo "<a href='$url'>Login with BitBucket!</a>";
}
