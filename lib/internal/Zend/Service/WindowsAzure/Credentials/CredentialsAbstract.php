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
 * @version    $Id: CredentialsAbstract.php 22773 2010-08-03 07:18:27Z maartenba $
 */

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
abstract class Zend_Service_WindowsAzure_Credentials_CredentialsAbstract
{
	/**
	 * Development storage account and key
	 */
	const DEVSTORE_ACCOUNT       = "devstoreaccount1";
	const DEVSTORE_KEY           = "Eby8vdM02xNOcqFlqUwJPLlmEtlCDXJ1OUzFT50uSRZ6IFsuFq2UVErCz4I6tq/K1SZFPTOtr/KBHBeksoGMGw==";
	
	/**
	 * HTTP header prefixes
	 */
	const PREFIX_PROPERTIES      = "x-ms-prop-";
	const PREFIX_METADATA        = "x-ms-meta-";
	const PREFIX_STORAGE_HEADER  = "x-ms-";
	
	/**
	 * Permissions
	 */
	const PERMISSION_READ        = "r";
	const PERMISSION_WRITE       = "w";
	const PERMISSION_DELETE      = "d";
	const PERMISSION_LIST        = "l";

	/**
	 * Account name for Windows Azure
	 *
	 * @var string
	 */
	protected $_accountName = '';
	
	/**
	 * Account key for Windows Azure
	 *
	 * @var string
	 */
	protected $_accountKey = '';
	
	/**
	 * Use path-style URI's
	 *
	 * @var boolean
	 */
	protected $_usePathStyleUri = false;
	
	/**
	 * Creates a new Zend_Service_WindowsAzure_Credentials_CredentialsAbstract instance
	 *
	 * @param string $accountName Account name for Windows Azure
	 * @param string $accountKey Account key for Windows Azure
	 * @param boolean $usePathStyleUri Use path-style URI's
	 */
	public function __construct(
		$accountName = Zend_Service_WindowsAzure_Credentials_CredentialsAbstract::DEVSTORE_ACCOUNT,
		$accountKey  = Zend_Service_WindowsAzure_Credentials_CredentialsAbstract::DEVSTORE_KEY,
		$usePathStyleUri = false
	) {
		$this->_accountName = $accountName;
		$this->_accountKey = base64_decode($accountKey);
		$this->_usePathStyleUri = $usePathStyleUri;
	}
	
	/**
	 * Set account name for Windows Azure
	 *
	 * @param  string $value
	 * @return Zend_Service_WindowsAzure_Credentials_CredentialsAbstract
	 */
	public function setAccountName($value = Zend_Service_WindowsAzure_Credentials_CredentialsAbstract::DEVSTORE_ACCOUNT)
	{
		$this->_accountName = $value;
		return $this;
	}
	
	/**
	 * Set account key for Windows Azure
	 *
	 * @param  string $value
	 * @return Zend_Service_WindowsAzure_Credentials_CredentialsAbstract
	 */
	public function setAccountkey($value = Zend_Service_WindowsAzure_Credentials_CredentialsAbstract::DEVSTORE_KEY)
	{
		$this->_accountKey = base64_decode($value);
		return $this;
	}
	
	/**
	 * Set use path-style URI's
	 *
	 * @param  boolean $value
	 * @return Zend_Service_WindowsAzure_Credentials_CredentialsAbstract
	 */
	public function setUsePathStyleUri($value = false)
	{
		$this->_usePathStyleUri = $value;
		return $this;
	}
	
	/**
	 * Sign request URL with credentials
	 *
	 * @param string $requestUrl Request URL
	 * @param string $resourceType Resource type
	 * @param string $requiredPermission Required permission
	 * @return string Signed request URL
	 */
	abstract public function signRequestUrl(
		$requestUrl = '',
		$resourceType = Zend_Service_WindowsAzure_Storage::RESOURCE_UNKNOWN,
		$requiredPermission = Zend_Service_WindowsAzure_Credentials_CredentialsAbstract::PERMISSION_READ
	);
	
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
	abstract public function signRequestHeaders(
		$httpVerb = Zend_Http_Client::GET,
		$path = '/',
		$queryString = '',
		$headers = null,
		$forTableStorage = false,
		$resourceType = Zend_Service_WindowsAzure_Storage::RESOURCE_UNKNOWN,
		$requiredPermission = Zend_Service_WindowsAzure_Credentials_CredentialsAbstract::PERMISSION_READ,
		$rawData = null
	);
	
	
	/**
	 * Prepare query string for signing
	 * 
	 * @param  string $value Original query string
	 * @return string        Query string for signing
	 */
	protected function _prepareQueryStringForSigning($value)
	{
	    // Return value
	    $returnValue = array();
	    
	    // Prepare query string
	    $queryParts = $this->_makeArrayOfQueryString($value);
	    foreach ($queryParts as $key => $value) {
	    	$returnValue[] = $key . '=' . $value;
	    }
	    
	    // Return
	    if (count($returnValue) > 0) {
	    	return '?' . implode('&', $returnValue);
	    } else {
	    	return '';
	    }
	}
	
	/**
	 * Make array of query string
	 * 
	 * @param  string $value Query string
	 * @return array         Array of key/value pairs
	 */
	protected function _makeArrayOfQueryString($value)
	{
		// Returnvalue
		$returnValue = array();
		
	    // Remove front ?     
   		if (strlen($value) > 0 && strpos($value, '?') === 0) {
    		$value = substr($value, 1);
    	}
    		
    	// Split parts
    	$queryParts = explode('&', $value);
    	foreach ($queryParts as $queryPart) {
    		$queryPart = explode('=', $queryPart, 2);
    		
    		if ($queryPart[0] != '') {
    			$returnValue[ $queryPart[0] ] = isset($queryPart[1]) ? $queryPart[1] : '';
    		}
    	}
    	
    	// Sort
    	ksort($returnValue);

    	// Return
		return $returnValue;
	}
	
	/**
	 * Returns an array value if the key is set, otherwide returns $valueIfNotSet
	 * 
	 * @param array $array
	 * @param mixed $key
	 * @param mixed $valueIfNotSet
	 * @return mixed
	 */
	protected function _issetOr($array, $key, $valueIfNotSet)
	{
		return isset($array[$key]) ? $array[$key] : $valueIfNotSet;
	}
}
