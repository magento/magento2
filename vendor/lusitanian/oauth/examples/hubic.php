<?php

/**
 * Hubic service.
 *
 * Example of retrieving an authentication token of the Hubic service
 *
 * @author      Pedro Amorim <contact@pamorim.fr>
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @link        https://api.hubic.com/docs/
 */

use OAuth\OAuth2\Service\Hubic;
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
    $servicesCredentials['hubic']['key'],
    $servicesCredentials['hubic']['secret'],
    $currentUri->getAbsoluteUri()
);

$scopes = array(
    Hubic::SCOPE_USAGE_GET,
    Hubic::SCOPE_ACCOUNT_GET,
    Hubic::SCOPE_GETALLLINKS_GET,
    Hubic::SCOPE_LINKS_ALL,
);

$serviceFactory->setHttpClient(new CurlClient);

// Instantiate the Hubic service using the credentials, http client and storage mechanism for the token
/** @var $hubicService Hubic */
$hubicService = $serviceFactory->createService('hubic', $credentials, $storage, $scopes);

if (!empty($_GET['code'])) {
    // This was a callback request from hubic, get the token
    $token = $hubicService->requestAccessToken($_GET['code']);

    // Send a request with it
    $result = json_decode($hubicService->request('https://api.hubic.com/1.0/account'), true);
    // Show some of the resultant data
    echo 'Hello '.ucfirst($result['firstname']).' '.strtoupper($result['lastname']).' ('.$result['email'].')';

} elseif (!empty($_GET['go']) && $_GET['go'] === 'go') {
    $url = $hubicService->getAuthorizationUri();
    // var_dump($url);
    header('Location: ' . $url);
} else {
    $url = $currentUri->getRelativeUri() . '?go=go';
    echo "<a href='$url'>Login with Hubic!</a>";
}
