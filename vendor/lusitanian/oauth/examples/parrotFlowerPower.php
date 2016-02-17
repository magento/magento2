<?php

/**
 * ParrotFlowerPower service.
 *
 * Example of retrieving an authentication token of the ParrotFlowerPower service
 *
 * @author  Pedro Amorim <contact@pamorim.fr>
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @link    https://flowerpowerdev.parrot.com/projects/flower-power-web-service-api/wiki
 */

use OAuth\OAuth2\Service\ParrotFlowerPower;
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
    $servicesCredentials['parrotFlowerPower']['key'],
    $servicesCredentials['parrotFlowerPower']['secret'],
    $currentUri->getAbsoluteUri()
);

$serviceFactory->setHttpClient(new CurlClient);

// Instantiate the ParrotFlowerPower service using the credentials, http client and storage mechanism for the token
$parrotFlowerPowerService = $serviceFactory->createService('parrotFlowerPower', $credentials, $storage);

if (!empty($_GET['code'])) {
    // This was a callback request from parrotFlowerPower, get the token
    $token = $parrotFlowerPowerService->requestAccessToken($_GET['code']);
    // Send a request with it
    $result = json_decode($parrotFlowerPowerService->request('https://apiflowerpower.parrot.com/user/v4/profile'), true);
    // Show some of the resultant data
    echo 'Hello '.$result['user_profile']['username'].' '.$result['user_profile']['email'];

} elseif (!empty($_GET['go']) && $_GET['go'] === 'go') {
    $url = $parrotFlowerPowerService->getAuthorizationUri();
    header('Location: ' . $url);
} else {
    $url = $currentUri->getRelativeUri() . '?go=go';
    echo "<a href='$url'>Login with ParrotFlowerPower!</a>";
}
