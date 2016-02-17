<?php

/**
 * Example of retrieving an authentication token of the DeviantArt service
 *
 * PHP version 5.4
 *
 * @author     Benjamin Bender <bb@codepoet.de>
 * @author     David Desberg <david@daviddesberg.com>
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 * @copyright  Copyright (c) 2012 The authors
 * @license    http://www.opensource.org/licenses/mit-license.html  MIT License
 */

use OAuth\OAuth2\Service\Facebook;
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
    $servicesCredentials['deviantart']['key'],
    $servicesCredentials['deviantart']['secret'],
    $currentUri->getAbsoluteUri()
);

// Instantiate the DeviantArt service using the credentials, http client and storage mechanism for the token
/** @var $deviantArtService DeviantArt */
$deviantArtService = $serviceFactory->createService('DeviantArt', $credentials, $storage, array('browse'));

if (!empty($_GET['code'])) {
    // This was a callback request from facebook, get the token
    $token = $deviantArtService->requestAccessToken($_GET['code']);

    // Send a request with it
    $result = json_decode($deviantArtService->request('/user/whoami'), true);

    // Show some of the resultant data
    echo 'Your DeviantArt username is: ' . $result['username'];

} elseif (!empty($_GET['go']) && $_GET['go'] === 'go') {
    $url = $deviantArtService->getAuthorizationUri();
    header('Location: ' . $url);
} else {
    $url = $currentUri->getRelativeUri() . '?go=go';
    echo "<a href='$url'>Login with DeviantArt!</a>";
}
