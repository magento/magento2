<?php

/**
 * Example of retrieving an authentication token of the Github service
 *
 * PHP version 5.4
 *
 * @author     David Desberg <david@daviddesberg.com>
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 * @copyright  Copyright (c) 2012 The authors
 * @license    http://www.opensource.org/licenses/mit-license.html  MIT License
 */

use OAuth\OAuth2\Service\GitHub;
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
    $servicesCredentials['github']['key'],
    $servicesCredentials['github']['secret'],
    $currentUri->getAbsoluteUri()
);

// Instantiate the GitHub service using the credentials, http client and storage mechanism for the token
/** @var $gitHub GitHub */
$gitHub = $serviceFactory->createService('GitHub', $credentials, $storage, array('user'));

if (!empty($_GET['code'])) {
    // This was a callback request from github, get the token
    $gitHub->requestAccessToken($_GET['code']);

    $result = json_decode($gitHub->request('user/emails'), true);

    echo 'The first email on your github account is ' . $result[0];

} elseif (!empty($_GET['go']) && $_GET['go'] === 'go') {
    $url = $gitHub->getAuthorizationUri();
    header('Location: ' . $url);

} else {
    $url = $currentUri->getRelativeUri() . '?go=go';
    echo "<a href='$url'>Login with Github!</a>";
}
