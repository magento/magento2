<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Service_WindowsAzure
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: SharedKey.php 23167 2010-10-19 17:53:31Z mabe $
 */

/**
 * @see Zend_Service_WindowsAzure_Credentials_CredentialsAbstract
 */
#require_once 'Zend/Service/WindowsAzure/Credentials/CredentialsAbstract.php';

/**
 * @see Zend_Service_WindowsAzure_Storage
 */
#require_once 'Zend/Service/WindowsAzure/Storage.php';

/**
 * @see Zend_Http_Client
 */
#require_once 'Zend/Http/Client.php';

/**
 * @see Zend_Service_WindowsAzure_Credentials_Exception
 */
#require_once 'Zend/Service/WindowsAzure/Credentials/Exception.php';

/**
 * @category   Zend
 * @package    Zend_Service_WindowsAzure
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */ 
class Zend_Service_WindowsAzure_Credentials_SharedKey
    extends Zend_Service_WindowsAzure_Credentials_CredentialsAbstract
{
    /**
	 * Sign request URL with credentials
	 *
	 * @param string $requestUrl Request URL
	 * @param string $resourceType Resource type
	 * @param string $requiredPermission Required permission
	 * @return string Signed request URL
	 */
	public function signRequestUrl(
		$requestUrl = '',
		$resourceType = Zend_Service_WindowsAzure_Storage::RESOURCE_UNKNOWN,
		$requiredPermission = Zend_Service_WindowsAzure_Credentials_CredentialsAbstract::PERMISSION_READ
	) {
	    return $requestUrl;
	}
	
	/**
	 * Sign request headers with credentials
	 *
	 * @param string $httpVerb HTTP verb the request will use
	 * @param string $path Path for the request
	 * @param string $queryString Query string for the request
	 * @param array $headers x-ms headers to add
	 * @param boolean $forTableStorage Is the request for table storage?
	 * @param string $resourceType Resource type
	 * @param string $requiredPermission Required permission
	 * @param mixed  $rawData Raw post data
	 * @return array Array of headers
	 */
	public function signRequestHeaders(
		$httpVerb = Zend_Http_Client::GET,
		$path = '/',
		$queryString = '',
		$headers = null,
		$forTableStorage = false,
		$resourceType = Zend_Service_WindowsAzure_Storage::RESOURCE_UNKNOWN,
		$requiredPermission = Zend_Service_WindowsAzure_Credentials_CredentialsAbstract::PERMISSION_READ,
		$rawData = null
	) {
		// http://github.com/sriramk/winazurestorage/blob/214010a2f8931bac9c96dfeb337d56fe084ca63b/winazurestorage.py

		// Table storage?
		if ($forTableStorage) {
			throw new Zend_Service_WindowsAzure_Credentials_Exception('The Windows Azure SDK for PHP does not support SharedKey authentication on table storage. Use SharedKeyLite authentication instead.');
		}
		
		// Determine path
		if ($this->_usePathStyleUri) {
			$path = substr($path, strpos($path, '/'));
		}

		// Determine query
		$queryString = $this->_prepareQueryStringForSigning($queryString);
	
		// Canonicalized headers
		$canonicalizedHeaders = array();
		
		// Request date
		$requestDate = '';
		if (isset($headers[Zend_Service_WindowsAzure_Credentials_CredentialsAbstract::PREFIX_STORAGE_HEADER . 'date'])) {
		    $requestDate = $headers[Zend_Service_WindowsAzure_Credentials_CredentialsAbstract::PREFIX_STORAGE_HEADER . 'date'];
		} else {
		    $requestDate = gmdate('D, d M Y H:i:s', time()) . ' GMT'; // RFC 1123
		    $canonicalizedHeaders[] = Zend_Service_WindowsAzure_Credentials_CredentialsAbstract::PREFIX_STORAGE_HEADER . 'date:' . $requestDate;
		}
		
		// Build canonicalized headers
		if ($headers !== null) {
			foreach ($headers as $header => $value) {
				if (is_bool($value)) {
					$value = $value === true ? 'True' : 'False';
				}

				$headers[$header] = $value;
				if (substr($header, 0, strlen(Zend_Service_WindowsAzure_Credentials_CredentialsAbstract::PREFIX_STORAGE_HEADER)) == Zend_Service_WindowsAzure_Credentials_CredentialsAbstract::PREFIX_STORAGE_HEADER) {
				    $canonicalizedHeaders[] = strtolower($header) . ':' . $value;
				}
			}
		}
		sort($canonicalizedHeaders);

		// Build canonicalized resource string
		$canonicalizedResource  = '/' . $this->_accountName;
		if ($this->_usePathStyleUri) {
			$canonicalizedResource .= '/' . $this->_accountName;
		}
		$canonicalizedResource .= $path;
		if ($queryString !== '') {
		    $queryStringItems = $this->_makeArrayOfQueryString($queryString);
		    foreach ($queryStringItems as $key => $value) {
		    	$canonicalizedResource .= "\n" . strtolower($key) . ':' . $value;
		    }
		}
		
		// Content-Length header
		$contentLength = '';
		if (strtoupper($httpVerb) != Zend_Http_Client::GET
			 && strtoupper($httpVerb) != Zend_Http_Client::DELETE
			 && strtoupper($httpVerb) != Zend_Http_Client::HEAD) {
			$contentLength = 0;
			
			if ($rawData !== null) {
				$contentLength = strlen($rawData);
			}
		}

		// Create string to sign   
		$stringToSign   = array();
		$stringToSign[] = strtoupper($httpVerb); 									// VERB
    	$stringToSign[] = $this->_issetOr($headers, 'Content-Encoding', '');		// Content-Encoding
    	$stringToSign[] = $this->_issetOr($headers, 'Content-Language', '');		// Content-Language
    	$stringToSign[] = $contentLength; 											// Content-Length
    	$stringToSign[] = $this->_issetOr($headers, 'Content-MD5', '');				// Content-MD5
    	$stringToSign[] = $this->_issetOr($headers, 'Content-Type', '');			// Content-Type
    	$stringToSign[] = "";														// Date
    	$stringToSign[] = $this->_issetOr($headers, 'If-Modified-Since', '');		// If-Modified-Since
    	$stringToSign[] = $this->_issetOr($headers, 'If-Match', '');				// If-Match
    	$stringToSign[] = $this->_issetOr($headers, 'If-None-Match', '');			// If-None-Match
    	$stringToSign[] = $this->_issetOr($headers, 'If-Unmodified-Since', '');		// If-Unmodified-Since
    	$stringToSign[] = $this->_issetOr($headers, 'Range', '');					// Range
    	
    	if (!$forTableStorage && count($canonicalizedHeaders) > 0) {
    		$stringToSign[] = implode("\n", $canonicalizedHeaders); // Canonicalized headers
    	}
    		
    	$stringToSign[] = $canonicalizedResource;		 			// Canonicalized resource
    	$stringToSign   = implode("\n", $stringToSign);
    	$signString     = base64_encode(hash_hmac('sha256', $stringToSign, $this->_accountKey, true));

    	// Sign request
    	$headers[Zend_Service_WindowsAzure_Credentials_CredentialsAbstract::PREFIX_STORAGE_HEADER . 'date'] = $requestDate;
    	$headers['Authorization'] = 'SharedKey ' . $this->_accountName . ':' . $signString;
    	
    	// Return headers
    	return $headers;
	}
}
