<?php

/**
 * Example of retrieving an authentication token of the Amazon service
 *
 * PHP version 5.4
 *
 * @author     Flávio Heleno <flaviohbatista@gmail.com>
 * @copyright  Copyright (c) 2012 The authors
 * @license    http://www.opensource.org/licenses/mit-license.html  MIT License
 */

use OAuth\OAuth2\Service\Amazon;
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
    $servicesCredentials['amazon']['key'],
    $servicesCredentials['amazon']['secret'],
    $currentUri->getAbsoluteUri()
);

// Instantiate the Amazon service using the credentials, http client, storage mechanism for the token and profile scope
/** @var $amazonService Amazon */
$amazonService = $serviceFactory->createService('amazon', $credentials, $storage, array('profile'));

if (!empty($_GET['code'])) {
    // This was a callback request from Amazon, get the token
    $token = $amazonService->requestAccessToken($_GET['code']);

    // Send a request with it
    $result = json_decode($amazonService->request('/user/profile'), true);

    // Show some of the resultant data
    echo 'Your unique Amazon user id is: ' . $result['user_id'] . ' and your name is ' . $result['name'];

} elseif (!empty($_GET['go']) && $_GET['go'] === 'go') {
    $url = $amazonService->getAuthorizationUri();
    header('Location: ' . $url);
} else {
    $url = $currentUri->getRelativeUri() . '?go=go';
    echo "<a href='$url'>Login with Amazon!</a>";
}
