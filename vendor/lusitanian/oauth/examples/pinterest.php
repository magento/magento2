<?php

/**
 * Example of retrieving an authentication token of the Pinterest service
 *
 * @author  Pedro Amorim <contact@pamorim.fr>
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @link    https://developers.pinterest.com/docs/api/overview/
 */

use OAuth\OAuth2\Service\Pinterest;
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
    $servicesCredentials['pinterest']['key'],
    $servicesCredentials['pinterest']['secret'],
    preg_replace('$http://$', 'https://', $currentUri->getAbsoluteUri()) // Pinterest require Https callback's url
);
$serviceFactory->setHttpClient(new CurlClient);
// Instantiate the Pinterest service using the credentials, http client and storage mechanism for the token
/** @var $pinterestService Pinterest */
$pinterestService = $serviceFactory->createService('pinterest', $credentials, $storage, [Pinterest::SCOPE_READ_PUBLIC]);

if (!empty($_GET['code'])) {
    // retrieve the CSRF state parameter
    $state = isset($_GET['state']) ? $_GET['state'] : null;
    // This was a callback request from pinterest, get the token
    $token = $pinterestService->requestAccessToken($_GET['code'], $state);
    // Show some of the resultant data
    $result = json_decode($pinterestService->request('v1/me/'), true);
    echo 'Hello ' . ucfirst($result['data']['first_name'])
    . ' ' . strtoupper($result['data']['last_name'])
    . ' your Pinterst Id is ' . $result['data']['id'];
} elseif (!empty($_GET['go']) && $_GET['go'] === 'go') {
    $url = $pinterestService->getAuthorizationUri();
    header('Location: ' . $url);
} else {
    $url = $currentUri->getRelativeUri() . '?go=go';
    echo "<a href='$url'>Login with Pinterest!</a>";
}
