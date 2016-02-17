<?php

/**
 * Example of retrieving an authentication token of the Box service
 *
 * PHP version 5.4
 *
 * @author     David Desberg <david@daviddesberg.com>
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 * @author     Antoine Corcy <contact@sbin.dk>
 * @copyright  Copyright (c) 2012 The authors
 * @license    http://www.opensource.org/licenses/mit-license.html  MIT License
 */

use OAuth\OAuth2\Service\Box;
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
    $servicesCredentials['box']['key'],
    $servicesCredentials['box']['secret'],
    $currentUri->getAbsoluteUri()
);

// Instantiate the Box service using the credentials, http client and storage mechanism for the token
/** @var $boxService Box */
$boxService = $serviceFactory->createService('box', $credentials, $storage);

if (!empty($_GET['code'])) {
    // retrieve the CSRF state parameter
    $state = isset($_GET['state']) ? $_GET['state'] : null;

    // This was a callback request from box, get the token
    $token = $boxService->requestAccessToken($_GET['code'], $state);

    // Send a request with it
    $result = json_decode($boxService->request('/users/me'), true);

    // Show some of the resultant data
    echo 'Your Box name is ' . $result['name'] . ' and your email is ' . $result['login'];

} elseif (!empty($_GET['go']) && $_GET['go'] === 'go') {
    $url = $boxService->getAuthorizationUri();
    // var_dump($url);
    header('Location: ' . $url);
} else {
    $url = $currentUri->getRelativeUri() . '?go=go';
    echo "<a href='$url'>Login with Box!</a>";
}
