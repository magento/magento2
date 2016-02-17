<?php

/**
 * Example of retrieving an authentication token from the RunKeeper service
 *
 * PHP version 5.4
 *
 * @copyright  Copyright (c) 2012 The authors
 * @license    http://www.opensource.org/licenses/mit-license.html  MIT License
 */

use OAuth\OAuth2\Service\RunKeeper;
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
    $servicesCredentials['runkeeper']['key'],
    $servicesCredentials['runkeeper']['secret'],
    $currentUri->getAbsoluteUri()
);

// Instantiate the Runkeeper service using the credentials, http client and storage mechanism for the token
/** @var $runkeeperService RunKeeper */
$runkeeperService = $serviceFactory->createService('RunKeeper', $credentials, $storage, array());

if (!empty($_GET['code'])) {
    // This was a callback request from RunKeeper, get the token
    $token = $runkeeperService->requestAccessToken($_GET['code']);

    // Send a request with it
    $result = json_decode($runkeeperService->request('/user'), true);

    // Show some of the resultant data
    echo 'Your unique RunKeeper user id is: ' . $result['userID'];

} elseif (!empty($_GET['go']) && $_GET['go'] === 'go') {
    $url = $runkeeperService->getAuthorizationUri();
    header('Location: ' . $url);
} else {
    $url = $currentUri->getRelativeUri() . '?go=go';
    echo "<a href='$url'>Login with RunKeeper!</a>";
}
