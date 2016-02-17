<?php

/**
 * Vimeo service.
 *
 * Example of retrieving an authentication token of the vimeo service
 *
 * @author      Pedro Amorim <contact@pamorim.fr>
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @link        https://developer.vimeo.com/api/authentication
 */

use OAuth\OAuth2\Service\Vimeo;
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
    $servicesCredentials['vimeo']['key'],
    $servicesCredentials['vimeo']['secret'],
    $currentUri->getAbsoluteUri()
);

// Instantiate the vimeo service using the credentials, http client and storage mechanism for the token
/** @var $vimeoService vimeo */
$vimeoService = $serviceFactory->createService('Vimeo', $credentials, $storage, [Vimeo::SCOPE_PUBLIC, Vimeo::SCOPE_PRIVATE]);

if (!empty($_GET['code'])) {
    // retrieve the CSRF state parameter
    $state = isset($_GET['state']) ? $_GET['state'] : null;
    // This was a callback request from vimeo, get the token
    $token = $vimeoService->requestAccessToken($_GET['code'], $state);
    // Send a request now that we have access token
    $result = json_decode($vimeoService->request('/me'));
    // Show some of the resultant data
    echo 'Your unique Vimeo account is <a href="'.$result->link.'">'.$result->uri.'</a> and your name is '.$result->name;

} elseif (!empty($_GET['go']) && $_GET['go'] === 'go') {
    $url = $vimeoService->getAuthorizationUri();
    header('Location: ' . $url);
} else {
    $url = $currentUri->getRelativeUri() . '?go=go';
    echo "<a href='$url'>Login with vimeo!</a>";
}
