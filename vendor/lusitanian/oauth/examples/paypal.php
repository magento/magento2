<?php

/**
 * Example of retrieving an authentication token of the PayPal service
 *
 * PHP version 5.4
 *
 * @author     Flávio Heleno <flaviohbatista@gmail.com>
 * @copyright  Copyright (c) 2012 The authors
 * @license    http://www.opensource.org/licenses/mit-license.html  MIT License
 */

use OAuth\OAuth2\Service\Paypal;
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
    $servicesCredentials['paypal']['key'],
    $servicesCredentials['paypal']['secret'],
    $currentUri->getAbsoluteUri()
);

// Instantiate the PayPal service using the credentials, http client, storage mechanism for the token and profile/openid scopes
/** @var $paypalService PayPal */
$paypalService = $serviceFactory->createService('paypal', $credentials, $storage, array('profile', 'openid'));

if (!empty($_GET['code'])) {
    // This was a callback request from PayPal, get the token
    $token = $paypalService->requestAccessToken($_GET['code']);

    // Send a request with it
    $result = json_decode($paypalService->request('/identity/openidconnect/userinfo/?schema=openid'), true);

    // Show some of the resultant data
    echo 'Your unique PayPal user id is: ' . $result['user_id'] . ' and your name is ' . $result['name'];

} elseif (!empty($_GET['go']) && $_GET['go'] === 'go') {
    $url = $paypalService->getAuthorizationUri();
    header('Location: ' . $url);
} else {
    $url = $currentUri->getRelativeUri() . '?go=go';
    echo "<a href='$url'>Login with PayPal!</a>";
}
