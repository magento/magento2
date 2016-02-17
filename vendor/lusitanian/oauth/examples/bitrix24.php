<?php

/**
 * Example of retrieving an authentication token of the Bitrix24 service
 *
 * PHP version 5.4
 *
 * @author     David Desberg <david@daviddesberg.com>
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 * @copyright  Copyright (c) 2012 The authors
 * @license    http://www.opensource.org/licenses/mit-license.html  MIT License
 */

use OAuth\OAuth2\Service\GitHub;
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
    $servicesCredentials['bitrix24']['key'],
    $servicesCredentials['bitrix24']['secret'],
    $currentUri->getAbsoluteUri()
);

// Instantiate the GitHub service using the credentials, http client and storage mechanism for the token

$yourDomain = new \OAuth\Common\Http\Uri\Uri('https://'.$servicesCredentials['bitrix24']['domain']);
/** @var $bitrix24 \OAuth\OAuth2\Service\Bitrix24 */
$bitrix24 = $serviceFactory->createService('Bitrix24', $credentials, $storage, array('user'), $yourDomain);

if (!empty($_GET['code'])) {
    // This was a callback request from bitrix24, get the token
    $bitrix24->requestAccessToken($_GET['code']);

    $response = json_decode($bitrix24->request('user.current'), true);
    $userInfo = $response['result'];

    // Show some of the resultant data
    echo 'Your email on your bitrix24 account is ' . $userInfo['EMAIL'];

} elseif (!empty($_GET['go']) && $_GET['go'] === 'go') {
    $url = $bitrix24->getAuthorizationUri();
    header('Location: ' . $url);

} else {
    $url = $currentUri->getRelativeUri() . '?go=go';
    echo "<a href='$url'>Login with Bitrix24!</a>";
}
