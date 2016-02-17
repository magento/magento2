<?php

/**
 * Example of retrieving an authentication token of the Yammer service and public messages
 *
 * PHP version 5.3
 *
 * @author     Viktor Aksionov <vaksionov@gmail.com>
 * @copyright  Copyright (c) 2014 The authors
 * @license    http://www.opensource.org/licenses/mit-license.html  MIT License
 */

use OAuth\OAuth2\Service\Yammer;
use OAuth\Common\Storage\Session;
use OAuth\Common\Consumer\Credentials;

include_once(__DIR__.'/OAuth/bootstrap.php');

$storage = new Session();

/**
 * In case if you don't use bootstrap from example folder uncomment lines below
 */
//$serviceFactory = new \OAuth\ServiceFactory(); 
/* Create a new instance of the URI class with the current URI, stripping the query string
 */
//$uriFactory = new \OAuth\Common\Http\Uri\UriFactory();
//$currentUri = $uriFactory->createFromSuperGlobalArray($_SERVER);
//$currentUri->setQuery('');

// Setup the credentials for the requests
$credentials = new Credentials(
    $servicesCredentials['yammer']['key'],
    $servicesCredentials['yammer']['secret'],
    $currentUri->getAbsoluteUri()
);

// Instantiate the Yammer service using the credentials, http client and storage mechanism for the token
$yammerService = $serviceFactory->createService('yammer', $credentials, $storage, array());

if (!empty($_GET['code'])) {
    // This was a callback request from yammer, get the token
    $token = $yammerService->requestAccessToken($_GET['code']);

    // yammer token, save somewhere and use it for all requests to yammer service
    echo $token->getAccessToken();
    
    // example of showing all public messages for current user
    // all endpoints can be find here: https://developer.yammer.com/restapi/#rest-networks
    $result = json_decode($yammerService->request('messages.json'), true);
    print_r($result);
} elseif (!empty($_GET['go']) && $_GET['go'] === 'go') {
    $url = $yammerService->getAuthorizationUri();
    header('Location: ' . $url);
} else {
    $url = $currentUri->getRelativeUri() . '?go=go';
    echo "<a href='$url'>Login with Yammer!</a>";
}
