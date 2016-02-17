<?php

/**
 * Example of making API calls for the Yahoo service
 *
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 * @copyright  Copyright (c) 2014 The authors
 * @license    http://www.opensource.org/licenses/mit-license.html  MIT License
 */

use OAuth\OAuth1\Service\Yahoo;
use OAuth\Common\Storage\Session;
use OAuth\Common\Consumer\Credentials;

/**
 * Bootstrap the example
 */
require_once __DIR__.'/bootstrap.php';

// Session storage
$storage = new Session();

// Setup the credentials for the requests
$credentials = new Credentials(
	$servicesCredentials['yahoo']['key'],
	$servicesCredentials['yahoo']['secret'],
	$currentUri->getAbsoluteUri()
);

// Instantiate the Yahoo service using the credentials, http client and storage mechanism for the token
$yahooService = $serviceFactory->createService('Yahoo', $credentials, $storage);

if (!empty($_GET['oauth_token'])) {
    $token = $storage->retrieveAccessToken('Yahoo');

    // This was a callback request from Yahoo, get the token
    $yahooService->requestAccessToken(
        $_GET['oauth_token'],
        $_GET['oauth_verifier'],
        $token->getRequestTokenSecret()
    );

    // Send a request now that we have access token
    $result = json_decode($yahooService->request('profile'));

    echo 'result: <pre>' . print_r($result, true) . '</pre>';

} elseif (!empty($_GET['go']) && $_GET['go'] === 'go') {
    // extra request needed for oauth1 to request a request token :-)
    $token = $yahooService->requestRequestToken();

    $url = $yahooService->getAuthorizationUri(array('oauth_token' => $token->getRequestToken()));
    header('Location: ' . $url);
} else {
    $url = $currentUri->getRelativeUri() . '?go=go';
    echo "<a href='$url'>Login with Yahoo!</a>";
}
