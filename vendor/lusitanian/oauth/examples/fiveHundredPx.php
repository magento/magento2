<?php

/**
 * 500px service.
 * 
 * Example of retrieving an authentication token of the fiveHundredPx service
 *
 *
 * @author      Pedro Ammorim <contact@pamorim.fr>
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @link        https://developers.500px.com/
 */

use OAuth\OAuth1\Service\fiveHundredPx;
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
    $servicesCredentials['fivehundredpx']['key'],
    $servicesCredentials['fivehundredpx']['secret'],
    $currentUri->getAbsoluteUri()
);

// Instantiate the fiveHundredPx service using the credentials, http client and storage mechanism for the token
/** @var $fivehundredpxService fiveHundredPx */
$fivehundredpxService = $serviceFactory->createService('FiveHundredPx', $credentials, $storage);

if (!empty($_GET['oauth_token'])) {
    $token = $storage->retrieveAccessToken('FiveHundredPx');

    // This was a callback request from fivehundredpx, get the token
    $fivehundredpxService->requestAccessToken(
        $_GET['oauth_token'],
        $_GET['oauth_verifier'],
        $token->getRequestTokenSecret()
    );
    // Send a request now that we have access token
    $result = json_decode($fivehundredpxService->request('https://api.500px.com/v1/users'), true);

    echo '<img src="'.$result['user']['avatars']['default']['http'].'"><br><b>'.$result['user']['username'].'</b><hr>';
    echo 'result: <pre>' . print_r($result, true) . '</pre>';

} elseif (!empty($_GET['go']) && $_GET['go'] === 'go') {
    // extra request needed for oauth1 to request a request token :-)
    $token = $fivehundredpxService->requestRequestToken();

    $url = $fivehundredpxService->getAuthorizationUri(array('oauth_token' => $token->getRequestToken()));
    header('Location: ' . $url);
} else {
    $url = $currentUri->getRelativeUri() . '?go=go';
    echo "<a href='$url'>Login with fiveHundredPx!</a>";
}
