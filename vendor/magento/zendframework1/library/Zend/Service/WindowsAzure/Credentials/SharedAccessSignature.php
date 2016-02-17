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
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_Service_WindowsAzure_Credentials_CredentialsAbstract
 */
#require_once 'Zend/Service/WindowsAzure/Credentials/CredentialsAbstract.php';

/**
 * @category   Zend
 * @package    Zend_Service_WindowsAzure
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_WindowsAzure_Credentials_SharedAccessSignature
    extends Zend_Service_WindowsAzure_Credentials_CredentialsAbstract
{
    /**
     * Permission set
     *
     * @var array
     */
    protected $_permissionSet = array();

	/**
	 * Creates a new Zend_Service_WindowsAzure_Credentials_SharedAccessSignature instance
	 *
	 * @param string $accountName Account name for Windows Azure
	 * @param string $accountKey Account key for Windows Azure
	 * @param boolean $usePathStyleUri Use path-style URI's
	 * @param array $permissionSet Permission set
	 */
	public function __construct(
		$accountName = Zend_Service_WindowsAzure_Credentials_CredentialsAbstract::DEVSTORE_ACCOUNT,
		$accountKey  = Zend_Service_WindowsAzure_Credentials_CredentialsAbstract::DEVSTORE_KEY,
		$usePathStyleUri = false, $permissionSet = array()
	) {
	    parent::__construct($accountName, $accountKey, $usePathStyleUri);
	    $this->_permissionSet = $permissionSet;
	}

	/**
	 * Get permission set
	 *
	 * @return array
	 */
    public function getPermissionSet()
	{
	    return $this->_permissionSet;
	}

	/**
	 * Set permisison set
	 *
	 * Warning: fine-grained permissions should be added prior to coarse-grained permissions.
	 * For example: first add blob permissions, end with container-wide permissions.
	 *
	 * Warning: the signed access signature URL must match the account name of the
	 * Zend_Service_WindowsAzure_Credentials_Zend_Service_WindowsAzure_Credentials_SharedAccessSignature instance
	 *
	 * @param  array $value Permission set
	 * @return void
	 */
    public function setPermissionSet($value = array())
	{
		foreach ($value as $url) {
			if (strpos($url, $this->_accountName) === false) {
				#require_once 'Zend/Service/WindowsAzure/Exception.php';
				throw new Zend_Service_WindowsAzure_Exception('The permission set can only contain URLs for the account name specified in the Zend_Service_WindowsAzure_Credentials_SharedAccessSignature instance.');
			}
		}
	    $this->_permissionSet = $value;
	}

    /**
     * Create signature
     *
     * @param string $path 		   Path for the request
     * @param string $resource     Signed resource - container (c) - blob (b)
     * @param string $permissions  Signed permissions - read (r), write (w), delete (d) and list (l)
     * @param string $start        The time at which the Shared Access Signature becomes valid.
     * @param string $expiry       The time at which the Shared Access Signature becomes invalid.
     * @param string $identifier   Signed identifier
     * @return string
     */
    public function createSignature(
    	$path = '/',
    	$resource = 'b',
    	$permissions = 'r',
    	$start = '',
    	$expiry = '',
    	$identifier = ''
    ) {
		// Determine path
		if ($this->_usePathStyleUri) {
			$path = substr($path, strpos($path, '/'));
		}

		// Add trailing slash to $path
		if (substr($path, 0, 1) !== '/') {
		    $path = '/' . $path;
		}

		// Build canonicalized resource string
		$canonicalizedResource  = '/' . $this->_accountName;
		/*if ($this->_usePathStyleUri) {
			$canonicalizedResource .= '/' . $this->_accountName;
		}*/
		$canonicalizedResource .= $path;

		// Create string to sign
		$stringToSign   = array();
		$stringToSign[] = $permissions;
    	$stringToSign[] = $start;
    	$stringToSign[] = $expiry;
    	$stringToSign[] = $canonicalizedResource;
    	$stringToSign[] = $identifier;

    	$stringToSign = implode("\n", $stringToSign);
    	$signature    = base64_encode(hash_hmac('sha256', $stringToSign, $this->_accountKey, true));

    	return $signature;
    }

    /**
     * Create signed query string
     *
     * @param string $path 		   Path for the request
     * @param string $queryString  Query string for the request
     * @param string $resource     Signed resource - container (c) - blob (b)
     * @param string $permissions  Signed permissions - read (r), write (w), delete (d) and list (l)
     * @param string $start        The time at which the Shared Access Signature becomes valid.
     * @param string $expiry       The time at which the Shared Access Signature becomes invalid.
     * @param string $identifier   Signed identifier
     * @return string
     */
    public function createSignedQueryString(
    	$path = '/',
    	$queryString = '',
    	$resource = 'b',
    	$permissions = 'r',
    	$start = '',
    	$expiry = '',
    	$identifier = ''
    ) {
        // Parts
        $parts = array();
        if ($start !== '') {
            $parts[] = 'st=' . urlencode($start);
        }
        $parts[] = 'se=' . urlencode($expiry);
        $parts[] = 'sr=' . $resource;
        $parts[] = 'sp=' . $permissions;
        if ($identifier !== '') {
            $parts[] = 'si=' . urlencode($identifier);
        }
        $parts[] = 'sig=' . urlencode($this->createSignature($path, $resource, $permissions, $start, $expiry, $identifier));

        // Assemble parts and query string
        if ($queryString != '') {
            $queryString .= '&';
	    }
        $queryString .= implode('&', $parts);

        return $queryString;
    }

    /**
	 * Permission matches request?
	 *
	 * @param string $permissionUrl Permission URL
	 * @param string $requestUrl Request URL
	 * @param string $resourceType Resource type
	 * @param string $requiredPermission Required permission
	 * @return string Signed request URL
	 */
    public function permissionMatchesRequest(
    	$permissionUrl = '',
    	$requestUrl = '',
    	$resourceType = Zend_Service_WindowsAzure_Storage::RESOURCE_UNKNOWN,
    	$requiredPermission = Zend_Service_WindowsAzure_Credentials_CredentialsAbstract::PERMISSION_READ
    ) {
        // Build requirements
        $requiredResourceType = $resourceType;
        if ($requiredResourceType == Zend_Service_WindowsAzure_Storage::RESOURCE_BLOB) {
            $requiredResourceType .= Zend_Service_WindowsAzure_Storage::RESOURCE_CONTAINER;
        }

        // Parse permission url
	    $parsedPermissionUrl = parse_url($permissionUrl);

	    // Parse permission properties
	    $permissionParts = explode('&', $parsedPermissionUrl['query']);

	    // Parse request url
	    $parsedRequestUrl = parse_url($requestUrl);

	    // Check if permission matches request
	    $matches = true;
	    foreach ($permissionParts as $part) {
	        list($property, $value) = explode('=', $part, 2);

	        if ($property == 'sr') {
	            $matches = $matches && (strpbrk($value, $requiredResourceType) !== false);
	        }

	    	if ($property == 'sp') {
	            $matches = $matches && (strpbrk($value, $requiredPermission) !== false);
	        }
	    }

	    // Ok, but... does the resource match?
	    $matches = $matches && (strpos($parsedRequestUrl['path'], $parsedPermissionUrl['path']) !== false);

        // Return
	    return $matches;
    }

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
	    // Look for a matching permission
	    foreach ($this->getPermissionSet() as $permittedUrl) {
	        if ($this->permissionMatchesRequest($permittedUrl, $requestUrl, $resourceType, $requiredPermission)) {
	            // This matches, append signature data
	            $parsedPermittedUrl = parse_url($permittedUrl);

	            if (strpos($requestUrl, '?') === false) {
	                $requestUrl .= '?';
	            } else {
	                $requestUrl .= '&';
	            }

	            $requestUrl .= $parsedPermittedUrl['query'];

	            // Return url
	            return $requestUrl;
	        }
	    }

	    // Return url, will be unsigned...
	    return $requestUrl;
	}

	/**
	 * Sign request with credentials
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
	    return $headers;
	}
}
