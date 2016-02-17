<?php

/**
 * Example of making API calls for the Redmine service
 * Developed against https://github.com/suer/redmine_oauth_provider
 * To create oauth credentials read the plugin documentation from
 * redmine_oauth_provider.
 *
 * @author     Patrick Herzberg <patrick@herzberg-digital.de>
 * @copyright  Copyright (c) 2015 The authors
 * @license    http://www.opensource.org/licenses/mit-license.html  MIT License
 *
 * Example based on the yahoo example
 */

/**
 * Bootstrap the example
 */
require_once __DIR__.'/bootstrap.php';

use OAuth\Common\Storage\Session;
use OAuth\Common\Consumer\Credentials;
use OAuth\Common\Http\Uri\Uri;

// Session storage
$storage = new Session();

// Setup the credentials for the requests
$credentials = new Credentials(
  $servicesCredentials['redmine']['key'],
  $servicesCredentials['redmine']['secret'],
  $currentUri->getAbsoluteUri()
);

// Instantiate the Redmine service using the credentials, http client, storage mechanism for the token and adding the base uri of the oauth provider
$redmineService = $serviceFactory->createService('Redmine', $credentials, $storage, array(), new Uri('https://redmine.example.dev/oauth/'));

if (!empty($_GET['oauth_token'])) {
    $token = $storage->retrieveAccessToken('Redmine');

    // This was a callback request from Redmine, get the token
    $redmineService->requestAccessToken(
        $_GET['oauth_token'],
        $_GET['oauth_verifier'],
        $token->getRequestTokenSecret()
    );

    // Send a request now that we have access token
    $result = json_decode($redmineService->request('user_info.json'));

    echo 'result: <pre>' . print_r($result, true) . '</pre>';

} elseif (!empty($_GET['go']) && $_GET['go'] === 'go') {
    // extra request needed for oauth1 to request a request token :-)
    $token = $redmineService->requestRequestToken();

    $url = $redmineService->getAuthorizationUri(array('oauth_token' => $token->getRequestToken()));
    header('Location: ' . $url);
} else {
    $url = 'http://example.dev/' . '?go=go';
    echo "<a href='$url'>Login with Redmine!</a>";
}