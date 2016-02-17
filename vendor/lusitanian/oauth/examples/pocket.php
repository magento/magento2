<?php

/**
 * Example of retrieving an authentication token of the Pocket service.
 *
 * @author     Christian Mayer <thefox21at@gmail.com>
 * @copyright  Copyright (c) 2014 Christian Mayer <thefox21at@gmail.com>
 * @license    http://www.opensource.org/licenses/mit-license.html  MIT License
 */

use OAuth\OAuth2\Service\Pocket;
use OAuth\Common\Storage\Session;
use OAuth\Common\Consumer\Credentials;
use OAuth\Common\Http\Client\CurlClient;

/**
 * Bootstrap the example
 */
require_once __DIR__.'/bootstrap.php';

$step = isset($_GET['step']) ? (int)$_GET['step'] : null;
$code = isset($_GET['code']) ? $_GET['code'] : null;

// Session storage
$storage = new Session();

// Setup the credentials for the requests
$credentials = new Credentials(
	$servicesCredentials['pocket']['key'],
	null, // Pocket API doesn't have a secret key. :S
	$currentUri->getAbsoluteUri().($code ? '?step=3&code='.$code : '')
);

// Instantiate the Pocket service using the credentials, http client and storage mechanism for the token
$pocketService = $serviceFactory->createService('Pocket', $credentials, $storage);

switch($step){
	default:
		print '<a href="'.$currentUri->getRelativeUri().'?step=1">Login with Pocket</a>';
		
		break;
	
	case 1:
		$code = $pocketService->requestRequestToken();
		header('Location: '.$currentUri->getRelativeUri().'?step=2&code='.$code);
		
		break;
	
	case 2:
		$url = $pocketService->getAuthorizationUri(array('request_token' => $code));
		header('Location: '.$url);
		
		break;
	
	case 3:
		$token = $pocketService->requestAccessToken($code);
		$accessToken = $token->getAccessToken();
		$extraParams = $token->getExtraParams();
		
		print 'User: '.$extraParams['username'].'<br />';
		print 'Access Token: '.$accessToken;
		break;
}
