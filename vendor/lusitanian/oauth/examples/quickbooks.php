<?php

/**
 * Example of connecting to Quickbooks service.
 *
 * PHP version 5.4
 *
 * @author     Elliot Chance <elliotchance@gmail.com>
 * @license    http://www.opensource.org/licenses/mit-license.html  MIT License
 */

use OAuth\OAuth1\Service\QuickBooks;
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
    $servicesCredentials['quickbooks']['key'],
    $servicesCredentials['quickbooks']['secret'],
    $currentUri->getAbsoluteUri()
);

// So we don't have to repeat ourselves.
$serviceName = 'QuickBooks';

// Instantiate the Quickbooks service using the credentials, http client and
// storage mechanism for the token
/** @var $quickbooksService QuickBooks */
$quickbooksService = $serviceFactory->createService(
    $serviceName, $credentials, $storage
);

if (!empty($_GET['oauth_token'])) {
    $token = $storage->retrieveAccessToken($serviceName);

    // This was a callback request from QuickBooks, get the token
    $quickbooksService->requestAccessToken(
        $_GET['oauth_token'],
        $_GET['oauth_verifier'],
        $token->getRequestTokenSecret()
    );

    // Send a request now that we have access token
    $companyId = $_GET['realmId'];
    $url = "/v3/company/$companyId/account/1";
    $result = json_decode($quickbooksService->request($url));

    echo 'result: <pre>' . print_r($result, true) . '</pre>';

} elseif (!empty($_GET['go']) && $_GET['go'] === 'go') {
    // extra request needed for oauth1 to request a request token :-)
    $token = $quickbooksService->requestRequestToken();

    $url = $quickbooksService->getAuthorizationUri(array('oauth_token' => $token->getRequestToken()));
    header('Location: ' . $url);
} else {
    $url = $currentUri->getRelativeUri() . '?go=go';
    echo "<a href='$url'>Login with QuickBooks!</a>";
}
