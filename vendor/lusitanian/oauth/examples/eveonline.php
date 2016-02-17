<?php

/**
 * Example of retrieving an authentication token of the Eve Online service
 * PHP version 5.4
 * @author     Micahel Cummings <mgcummings@yahoo.com>
 * @copyright  Copyright (c) 2014 The authors
 * @license    http://www.opensource.org/licenses/mit-license.html  MIT License
 */

use OAuth\Common\Consumer\Credentials;
use OAuth\Common\Storage\Session;
use OAuth\OAuth2\Service\EveOnline;

/**
 * Bootstrap the example
 */
require_once __DIR__ . '/bootstrap.php';

// Session storage
$storage = new Session();

// Setup the credentials for the requests
$credentials = new Credentials(
    $servicesCredentials['eveonline']['key'],
    $servicesCredentials['eveonline']['secret'],
    $currentUri->getAbsoluteUri()
);

// Instantiate the Eve Online service using the credentials, http client, storage mechanism for the token and profile scope
/** @var EveOnline $eveService */
$eveService = $serviceFactory->createService('EveOnline', $credentials, $storage, array(''));

if (!empty($_GET['code'])) {
    // This was a callback request from Eve Online, get the token
    $token = $eveService->requestAccessToken($_GET['code']);

    // Send a request with it
    $result = json_decode($eveService->request('/oauth/verify'), true);

    // Show some of the resultant data
    print 'CharacterName: ' . $result['CharacterName'] . PHP_EOL
        . 'CharacterID: ' . $result['CharacterID'] . PHP_EOL
        . 'ExpiresOn: ' . $result['ExpiresOn'] . PHP_EOL
        . 'Scopes: ' . $result['Scopes'] . PHP_EOL
        . 'TokenType: ' . $result['TokenType'] . PHP_EOL
        . 'CharacterOwnerHash: ' . $result['CharacterOwnerHash'] . PHP_EOL;

} elseif (!empty($_GET['go']) && $_GET['go'] === 'go') {
    $url = $eveService->getAuthorizationUri();
    header('Location: ' . $url);
} else {
    $url = $currentUri->getRelativeUri() . '?go=go';
    print "<a href='$url'>Login with Eve Online!</a>";
}
