<?php

/**
 * Delicious service.
 *
 * Example of retrieving an authentication token of the Delicious service
 *
 * @author  Pedro Amorim <contact@pamorim.fr>
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @link    https://github.com/SciDevs/delicious-api/blob/master/api/oauth.md
 */

use OAuth\OAuth2\Service\Delicious;
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
    $servicesCredentials['delicious']['key'],
    $servicesCredentials['delicious']['secret'],
    $currentUri->getAbsoluteUri()
);
// Use Curl HTTP Client
$serviceFactory->setHttpClient(new CurlClient);

// Instantiate the Delicious service using the credentials, http client and storage mechanism for the token
$deliciousService = $serviceFactory->createService('delicious', $credentials, $storage);

if (!empty($_GET['code'])) {
    // This was a callback request from delicious, get the token
    $token = $deliciousService->requestAccessToken($_GET['code']);
    // Show some of the resultant data
    echo 'Your Delicious access_token is : '.$token->getAccessToken()."\n";
    // Fetch recent post
    $xml = simplexml_load_string($deliciousService->request('/posts/recent'));
    $json = json_encode($xml);
    $array = json_decode($json, true);
    echo "Your recents posts saved are : \n";
    foreach ($array['post'] as $key => $value) {
        echo $value['@attributes']['description'].' ('.$value['@attributes']['href'].')'."\n";
    }

} elseif (!empty($_GET['go']) && $_GET['go'] === 'go') {
    $url = $deliciousService->getAuthorizationUri();
    // var_dump($url);
    header('Location: ' . $url);
} else {
    $url = $currentUri->getRelativeUri() . '?go=go';
    echo "<a href='$url'>Login with Delicious!</a>";
}
