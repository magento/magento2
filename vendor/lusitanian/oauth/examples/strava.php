<?php

/**
 * Example of retrieving an authentication token of the Strava service
 *
 * PHP version 5.4
 *
 * @author     David Desberg <david@daviddesberg.com>
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 * @copyright  Copyright (c) 2012 The authors
 * @license    http://www.opensource.org/licenses/mit-license.html  MIT License
 */

use OAuth\OAuth2\Service\Strava;
use OAuth\Common\Storage\Session;
use OAuth\Common\Consumer\Credentials;

use OAuth\Common\Http\Client\CurlClient;
/**
 * Bootstrap the example
 */
require_once __DIR__ . '/bootstrap.php';

// Session storage
$storage = new Session();

// Setup the credentials for the requests
$credentials = new Credentials(
    $servicesCredentials['strava']['key'],
    $servicesCredentials['strava']['secret'],
    $currentUri->getAbsoluteUri()
);
$scopes = array(
    // Strava::SCOPE_WRITE,
    // Strava::SCOPE_VIEW_PRIVATE,
);
$serviceFactory->setHttpClient(new CurlClient());

// Instantiate the Strava service using the credentials, http client and storage mechanism for the token
/** @var $stravaService Strava */
$stravaService = $serviceFactory->createService('strava', $credentials, $storage, $scopes);

// Force approuval
$stravaService->setApprouvalPrompt('force');

if (!empty($_GET['code'])) {
    // This was a callback request from strava, get the token
    $token = $stravaService->requestAccessToken($_GET['code']);
    // Send a request with it
    $result = json_decode($stravaService->request('/athlete'), true);

    // Show some of the resultant data
    echo 'Your Strava user id is: ' . $result['id'] . ' and your name is ' . $result['firstname'] . ' ' . $result['lastname'] . '!';

} elseif (!empty($_GET['go']) && $_GET['go'] === 'go') {
    $url = $stravaService->getAuthorizationUri();
    header('Location: ' . $url);
} else {
    $url = $currentUri->getRelativeUri() . '?go=go';
    echo "<a href='$url'>Login with Strava!</a>";
}
