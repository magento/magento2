<?php

/**
 * Example of retrieving an authentication token of the Buffer service
 *
 * PHP version 5.4
 *
 * @author     David Desberg <david@daviddesberg.com>
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 * @copyright  Copyright (c) 2012 The authors
 * @license    http://www.opensource.org/licenses/mit-license.html  MIT License
 */

use OAuth\OAuth2\Service\Buffer;
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
    $servicesCredentials['buffer']['key'],
    $servicesCredentials['buffer']['secret'],
    $currentUri->getAbsoluteUri()
);

// Instantiate the buffer service using the credentials, http client and storage mechanism for the token
/** @var $bufferService buffer */
$bufferService = $serviceFactory->createService('buffer', $credentials, $storage);

if (!empty($_GET['code'])) {
    // This was a callback request from buffer, get the token
    $bufferService->requestAccessToken($_GET['code']);

    // Send a request with it
    $result = json_decode($bufferService->request('user.json'), true);

    // Show some of the resultant data
    echo 'Your unique user id is: ' . $result['id'] . ' and your plan is ' . $result['plan'];

} elseif (!empty($_GET['go']) && $_GET['go'] === 'go') {
    $url = $bufferService->getAuthorizationUri();
    header('Location: ' . $url);
} else {
    $url = $currentUri->getRelativeUri() . '?go=go';
    echo "<a href='$url'>Login with buffer!</a>";
}
