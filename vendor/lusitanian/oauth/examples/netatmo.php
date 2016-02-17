<?php

/**
 * Netatmo service.
 *
 * Example of retrieving an authentication token of the Netatmo service
 *
 * @author  Pedro Amorim <contact@pamorim.fr>
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @link    https://dev.netatmo.com/doc/
 */

use OAuth\OAuth2\Service\Netatmo;
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
    $servicesCredentials['netatmo']['key'],
    $servicesCredentials['netatmo']['secret'],
    $currentUri->getAbsoluteUri()
);

$serviceFactory->setHttpClient(new CurlClient);

// Instantiate the Netatmo service using the credentials, http client and storage mechanism for the token
$NetatmoService = $serviceFactory->createService('Netatmo', $credentials, $storage);

if (!empty($_GET['code'])) {
    // retrieve the CSRF state parameter
    $state = isset($_GET['state']) ? $_GET['state'] : null;
    // This was a callback request from Netatmo, get the token
    $token = $NetatmoService->requestAccessToken($_GET['code'], $state);
    // Send a request with it
    $result = json_decode($NetatmoService->request('getuser'), true);
    // Show some of the resultant data
    echo 'Hello '.$result['body']['mail'];
} elseif (!empty($_GET['go']) && $_GET['go'] === 'go') {
    $url = $NetatmoService->getAuthorizationUri();
    header('Location: ' . $url);
} else {
    $url = $currentUri->getRelativeUri() . '?go=go';
    echo "<a href='$url'>Login with Netatmo!</a>";
}
