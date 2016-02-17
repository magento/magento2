<?php

/**
 * Example of retrieving an authentication token of the Dailymotion service
 *
 * PHP version 5.4
 *
 * @author     Mouhamed SEYE <mouhamed@seye.pro>
 * @copyright  Copyright (c) 2012 The authors
 * @license    http://www.opensource.org/licenses/mit-license.html  MIT License
 */

use OAuth\OAuth2\Service\Dailymotion;
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
    $servicesCredentials['dailymotion']['key'],
    $servicesCredentials['dailymotion']['secret'],
    $currentUri->getAbsoluteUri()
);

// Instantiate the Dailymotion service using the credentials, http client, storage mechanism for the token and email scope
/** @var $dailymotionService Dailymotion */
$dailymotionService = $serviceFactory->createService('dailymotion', $credentials, $storage, array('email'));

if (!empty($_GET['code'])) {
    // This was a callback request from Dailymotion, get the token
    $token = $dailymotionService->requestAccessToken($_GET['code']);

    // Send a request with it
    $result = json_decode($dailymotionService->request('/me?fields=email,id'), true);

    // Show some of the resultant data
    echo 'Your unique Dailymotion user id is: ' . $result['id'] . ' and your email is ' . $result['email'];

} elseif (!empty($_GET['go']) && $_GET['go'] === 'go') {
    $url = $dailymotionService->getAuthorizationUri();
    header('Location: ' . $url);
} else {
    $url = $currentUri->getRelativeUri() . '?go=go';
    echo "<a href='$url'>Login with Dailymotion!</a>";
}
