<?php

/**
 * Example of retrieving an authentication token of the Nest service
 *
 * @author  Pedro Amorim <contact@pamorim.fr>
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @link    https://developer.nest.com/documentation
 */

use OAuth\OAuth2\Service\Nest;
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
    $servicesCredentials['nest']['key'],
    $servicesCredentials['nest']['secret'],
    $currentUri->getAbsoluteUri()
);

$serviceFactory->setHttpClient(new CurlClient);
// Instantiate the Nest service using the credentials, http client and storage mechanism for the token
/** @var $nestService Nest */
$nestService = $serviceFactory->createService('nest', $credentials, $storage);

if (!empty($_GET['code'])) {
    // retrieve the CSRF state parameter
    $state = isset($_GET['state']) ? $_GET['state'] : null;
    // This was a callback request from nest, get the token
    $token = $nestService->requestAccessToken($_GET['code'], $state);
    // Show some of the resultant data
    $result = json_decode($nestService->request('/devices'), true);
    echo 'Your devices informations ' . print_r($result, true);
} elseif (!empty($_GET['go']) && $_GET['go'] === 'go') {
    $url = $nestService->getAuthorizationUri();
    header('Location: ' . $url);
} else {
    $url = $currentUri->getRelativeUri() . '?go=go';
    echo "<a href='$url'>Login with Nest!</a>";
}
