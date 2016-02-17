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
 * @subpackage Management
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_Http_Client
 */
 #require_once 'Zend/Http/Client.php';

 /**
 * @see Zend_Service_WindowsAzure_RetryPolicy_RetryPolicyAbstract
 */
 #require_once 'Zend/Service/WindowsAzure/RetryPolicy/RetryPolicyAbstract.php';

 /**
 * @see Zend_Service_SqlAzure_Management_ServerInstance
 */
 #require_once 'Zend/Service/SqlAzure/Management/ServerInstance.php';

 /**
 * @see Zend_Service_SqlAzure_Management_FirewallRuleInstance
 */
 #require_once 'Zend/Service/SqlAzure/Management/FirewallRuleInstance.php';

 /** @see Zend_Xml_Security */
 #require_once 'Zend/Xml/Security.php';

/**
 * @category   Zend
 * @package    Zend_Service_SqlAzure
 * @subpackage Management
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_SqlAzure_Management_Client
{
	/**
	 * Management service URL
	 */
	const URL_MANAGEMENT        = "https://management.database.windows.net:8443";

	/**
	 * Operations
	 */
	const OP_OPERATIONS                = "operations";
	const OP_SERVERS                   = "servers";
	const OP_FIREWALLRULES             = "firewallrules";

	/**
	 * Current API version
	 *
	 * @var string
	 */
	protected $_apiVersion = '1.0';

	/**
	 * Subscription ID
	 *
	 * @var string
	 */
	protected $_subscriptionId = '';

	/**
	 * Management certificate path (.PEM)
	 *
	 * @var string
	 */
	protected $_certificatePath = '';

	/**
	 * Management certificate passphrase
	 *
	 * @var string
	 */
	protected $_certificatePassphrase = '';

	/**
	 * Zend_Http_Client channel used for communication with REST services
	 *
	 * @var Zend_Http_Client
	 */
	protected $_httpClientChannel = null;

	/**
	 * Zend_Service_WindowsAzure_RetryPolicy_RetryPolicyAbstract instance
	 *
	 * @var Zend_Service_WindowsAzure_RetryPolicy_RetryPolicyAbstract
	 */
	protected $_retryPolicy = null;

	/**
	 * Returns the last request ID
	 *
	 * @var string
	 */
	protected $_lastRequestId = null;

	/**
	 * Creates a new Zend_Service_SqlAzure_Management instance
	 *
	 * @param string $subscriptionId Subscription ID
	 * @param string $certificatePath Management certificate path (.PEM)
	 * @param string $certificatePassphrase Management certificate passphrase
     * @param Zend_Service_WindowsAzure_RetryPolicy_RetryPolicyAbstract $retryPolicy Retry policy to use when making requests
	 */
	public function __construct(
		$subscriptionId,
		$certificatePath,
		$certificatePassphrase,
		Zend_Service_WindowsAzure_RetryPolicy_RetryPolicyAbstract $retryPolicy = null
	) {
		$this->_subscriptionId = $subscriptionId;
		$this->_certificatePath = $certificatePath;
		$this->_certificatePassphrase = $certificatePassphrase;

		$this->_retryPolicy = $retryPolicy;
		if (is_null($this->_retryPolicy)) {
		    $this->_retryPolicy = Zend_Service_WindowsAzure_RetryPolicy_RetryPolicyAbstract::noRetry();
		}

		// Setup default Zend_Http_Client channel
		$options = array(
		    'adapter'       => 'Zend_Http_Client_Adapter_Socket',
		    'ssltransport'  => 'ssl',
			'sslcert'       => $this->_certificatePath,
			'sslpassphrase' => $this->_certificatePassphrase,
			'sslusecontext' => true,
		);
		if (function_exists('curl_init')) {
			// Set cURL options if cURL is used afterwards
			$options['curloptions'] = array(
					CURLOPT_FOLLOWLOCATION => true,
					CURLOPT_TIMEOUT => 120,
			);
		}
		$this->_httpClientChannel = new Zend_Http_Client(null, $options);
	}

	/**
	 * Set the HTTP client channel to use
	 *
	 * @param Zend_Http_Client_Adapter_Interface|string $adapterInstance Adapter instance or adapter class name.
	 */
	public function setHttpClientChannel($adapterInstance = 'Zend_Http_Client_Adapter_Socket')
	{
		$this->_httpClientChannel->setAdapter($adapterInstance);
	}

    /**
     * Retrieve HTTP client channel
     *
     * @return Zend_Http_Client_Adapter_Interface
     */
    public function getHttpClientChannel()
    {
        return $this->_httpClientChannel;
    }

	/**
	 * Returns the Windows Azure subscription ID
	 *
	 * @return string
	 */
	public function getSubscriptionId()
	{
		return $this->_subscriptionId;
	}

	/**
	 * Returns the last request ID.
	 *
	 * @return string
	 */
	public function getLastRequestId()
	{
		return $this->_lastRequestId;
	}

	/**
	 * Get base URL for creating requests
	 *
	 * @return string
	 */
	public function getBaseUrl()
	{
		return self::URL_MANAGEMENT . '/' . $this->_subscriptionId;
	}

	/**
	 * Perform request using Zend_Http_Client channel
	 *
	 * @param string $path Path
	 * @param string $queryString Query string
	 * @param string $httpVerb HTTP verb the request will use
	 * @param array $headers x-ms headers to add
	 * @param mixed $rawData Optional RAW HTTP data to be sent over the wire
	 * @return Zend_Http_Response
	 */
	protected function _performRequest(
		$path = '/',
		$queryString = '',
		$httpVerb = Zend_Http_Client::GET,
		$headers = array(),
		$rawData = null
	) {
	    // Clean path
		if (strpos($path, '/') !== 0) {
			$path = '/' . $path;
		}

		// Clean headers
		if (is_null($headers)) {
		    $headers = array();
		}

		// Ensure cUrl will also work correctly:
		//  - disable Content-Type if required
		//  - disable Expect: 100 Continue
		if (!isset($headers["Content-Type"])) {
			$headers["Content-Type"] = '';
		}
		//$headers["Expect"] = '';

		// Add version header
		$headers['x-ms-version'] = $this->_apiVersion;

		// URL encoding
		$path           = self::urlencode($path);
		$queryString    = self::urlencode($queryString);

		// Generate URL and sign request
		$requestUrl     = $this->getBaseUrl() . $path . $queryString;
		$requestHeaders = $headers;

		// Prepare request
		$this->_httpClientChannel->resetParameters(true);
		$this->_httpClientChannel->setUri($requestUrl);
		$this->_httpClientChannel->setHeaders($requestHeaders);
		$this->_httpClientChannel->setRawData($rawData);

		// Execute request
		$response = $this->_retryPolicy->execute(
		    array($this->_httpClientChannel, 'request'),
		    array($httpVerb)
		);

		// Store request id
		$this->_lastRequestId = $response->getHeader('x-ms-request-id');

		return $response;
	}

	/**
	 * Parse result from Zend_Http_Response
	 *
	 * @param Zend_Http_Response $response Response from HTTP call
	 * @return object
	 * @throws Zend_Service_WindowsAzure_Exception
	 */
	protected function _parseResponse(Zend_Http_Response $response = null)
	{
		if (is_null($response)) {
			#require_once 'Zend/Service/SqlAzure/Exception.php';
			throw new Zend_Service_SqlAzure_Exception('Response should not be null.');
		}

        $xml = @Zend_Xml_Security::scan($response->getBody());

        if ($xml !== false) {
            // Fetch all namespaces
            $namespaces = array_merge($xml->getNamespaces(true), $xml->getDocNamespaces(true));

            // Register all namespace prefixes
            foreach ($namespaces as $prefix => $ns) {
                if ($prefix != '') {
                    $xml->registerXPathNamespace($prefix, $ns);
                }
            }
        }

        return $xml;
	}

	/**
	 * URL encode function
	 *
	 * @param  string $value Value to encode
	 * @return string        Encoded value
	 */
	public static function urlencode($value)
	{
	    return str_replace(' ', '%20', $value);
	}

    /**
     * Builds a query string from an array of elements
     *
     * @param array     Array of elements
     * @return string   Assembled query string
     */
    public static function createQueryStringFromArray($queryString)
    {
    	return count($queryString) > 0 ? '?' . implode('&', $queryString) : '';
    }

	/**
	 * Get error message from Zend_Http_Response
	 *
	 * @param Zend_Http_Response $response Repsonse
	 * @param string $alternativeError Alternative error message
	 * @return string
	 */
	protected function _getErrorMessage(Zend_Http_Response $response, $alternativeError = 'Unknown error.')
	{
		$response = $this->_parseResponse($response);
		if ($response && $response->Message) {
			return (string)$response->Message;
		} else {
			return $alternativeError;
		}
	}

	/**
	 * The Create Server operation adds a new SQL Azure server to a subscription.
	 *
	 * @param string $administratorLogin Administrator login.
	 * @param string $administratorPassword Administrator password.
	 * @param string $location Location of the server.
	 * @return Zend_Service_SqlAzure_Management_ServerInstance Server information.
	 * @throws Zend_Service_SqlAzure_Management_Exception
	 */
	public function createServer($administratorLogin, $administratorPassword, $location)
	{
		if ($administratorLogin == '' || is_null($administratorLogin)) {
                    #require_once 'Zend/Service/SqlAzure/Management/Exception.php';
                    throw new Zend_Service_SqlAzure_Management_Exception('Administrator login should be specified.');
                }
		if ($administratorPassword == '' || is_null($administratorPassword)) {
                    #require_once 'Zend/Service/SqlAzure/Management/Exception.php';
                    throw new Zend_Service_SqlAzure_Management_Exception('Administrator password should be specified.');
                }
                if (is_null($location) && is_null($affinityGroup)) {
                    #require_once 'Zend/Service/SqlAzure/Management/Exception.php';
                    throw new Zend_Service_SqlAzure_Management_Exception('Please specify a location for the server.');
                }

                $response = $this->_performRequest(self::OP_SERVERS, '',
    		Zend_Http_Client::POST,
    		array('Content-Type' => 'application/xml; charset=utf-8'),
    		'<Server xmlns="http://schemas.microsoft.com/sqlazure/2010/12/"><AdministratorLogin>' . $administratorLogin . '</AdministratorLogin><AdministratorLoginPassword>' . $administratorPassword . '</AdministratorLoginPassword><Location>' . $location . '</Location></Server>');

                if ($response->isSuccessful()) {
			$xml = $this->_parseResponse($response);

			return new Zend_Service_SqlAzure_Management_ServerInstance(
				(string)$xml,
				$administratorLogin,
				$location
			);
                } else {
			#require_once 'Zend/Service/SqlAzure/Management/Exception.php';
			throw new Zend_Service_SqlAzure_Management_Exception($this->_getErrorMessage($response, 'Resource could not be accessed.'));
		}
	}

	/**
	 * The Get Servers operation enumerates SQL Azure servers that are provisioned for a subscription.
	 *
	 * @return array An array of Zend_Service_SqlAzure_Management_ServerInstance.
	 * @throws Zend_Service_SqlAzure_Management_Exception
	 */
	public function listServers()
	{
            $response = $this->_performRequest(self::OP_SERVERS);

            if ($response->isSuccessful()) {
		$xml = $this->_parseResponse($response);
		$xmlServices = null;

                if (!$xml->Server) {
                    return array();
		}
		if (count($xml->Server) > 1) {
    		    $xmlServices = $xml->Server;
    		} else {
    		    $xmlServices = array($xml->Server);
    		}

		$services = array();
		if (!is_null($xmlServices)) {

                    for ($i = 0; $i < count($xmlServices); $i++) {
                        $services[] = new Zend_Service_SqlAzure_Management_ServerInstance(
                                	    (string)$xmlServices[$i]->Name,
					    (string)$xmlServices[$i]->AdministratorLogin,
					    (string)$xmlServices[$i]->Location
					);
                    }
		}
		return $services;
            } else {
		#require_once 'Zend/Service/SqlAzure/Management/Exception.php';
		throw new Zend_Service_SqlAzure_Management_Exception($this->_getErrorMessage($response, 'Resource could not be accessed.'));
            }
	}

	/**
	 * The Drop Server operation drops a SQL Azure server from a subscription.
	 *
	 * @param string $serverName Server to drop.
	 * @throws Zend_Service_SqlAzure_Management_Exception
	 */
	public function dropServer($serverName)
	{
            if ($serverName == '' || is_null($serverName)) {
                #require_once 'Zend/Service/SqlAzure/Management/Exception.php';
                throw new Zend_Service_SqlAzure_Management_Exception('Server name should be specified.');
            }

            $response = $this->_performRequest(self::OP_SERVERS . '/' . $serverName, '', Zend_Http_Client::DELETE);

            if (!$response->isSuccessful()) {
		#require_once 'Zend/Service/SqlAzure/Management/Exception.php';
		throw new Zend_Service_SqlAzure_Management_Exception($this->_getErrorMessage($response, 'Resource could not be accessed.'));
            }
	}

	/**
	 * The Set Server Administrator Password operation sets the administrative password of a SQL Azure server for a subscription.
	 *
	 * @param string $serverName Server to set password for.
	 * @param string $administratorPassword Administrator password.
	 * @throws Zend_Service_SqlAzure_Management_Exception
	 */
	public function setAdministratorPassword($serverName, $administratorPassword)
	{
            if ($serverName == '' || is_null($serverName)) {
		#require_once 'Zend/Service/SqlAzure/Management/Exception.php';
    		throw new Zend_Service_SqlAzure_Management_Exception('Server name should be specified.');
            }
            if ($administratorPassword == '' || is_null($administratorPassword)) {
                #require_once 'Zend/Service/SqlAzure/Management/Exception.php';
    		throw new Zend_Service_SqlAzure_Management_Exception('Administrator password should be specified.');
            }

            $response = $this->_performRequest(self::OP_SERVERS . '/' . $serverName, '?op=ResetPassword',
    		Zend_Http_Client::POST,
    		array('Content-Type' => 'application/xml; charset=utf-8'),
    		'<AdministratorLoginPassword xmlns="http://schemas.microsoft.com/sqlazure/2010/12/">' . $administratorPassword . '</AdministratorLoginPassword>');

            if (!$response->isSuccessful()) {
		#require_once 'Zend/Service/SqlAzure/Management/Exception.php';
		throw new Zend_Service_SqlAzure_Management_Exception($this->_getErrorMessage($response, 'Resource could not be accessed.'));
            }
	}

	/**
	 * The Set Server Firewall Rule operation updates an existing firewall rule or adds a new firewall rule for a SQL Azure server that belongs to a subscription.
	 *
	 * @param string $serverName Server name.
	 * @param string $ruleName Firewall rule name.
	 * @param string $startIpAddress Start IP address.
	 * @param string $endIpAddress End IP address.
	 * @return Zend_Service_SqlAzure_Management_FirewallRuleInstance
	 * @throws Zend_Service_SqlAzure_Management_Exception
	 */
	public function createFirewallRule($serverName, $ruleName, $startIpAddress, $endIpAddress)
	{
            if ($serverName == '' || is_null($serverName)) {
                #require_once 'Zend/Service/SqlAzure/Management/Exception.php';
                throw new Zend_Service_SqlAzure_Management_Exception('Server name should be specified.');
            }
            if ($ruleName == '' || is_null($ruleName)) {
                #require_once 'Zend/Service/SqlAzure/Management/Exception.php';
                throw new Zend_Service_SqlAzure_Management_Exception('Rule name should be specified.');
            }
            if ($startIpAddress == '' || is_null($startIpAddress) || !filter_var($startIpAddress, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                #require_once 'Zend/Service/SqlAzure/Management/Exception.php';
                throw new Zend_Service_SqlAzure_Management_Exception('Start IP address should be specified.');
            }
            if ($endIpAddress == '' || is_null($endIpAddress) || !filter_var($endIpAddress, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                #require_once 'Zend/Service/SqlAzure/Management/Exception.php';
                throw new Zend_Service_SqlAzure_Management_Exception('End IP address should be specified.');
            }

            $response = $this->_performRequest(self::OP_SERVERS . '/' . $serverName . '/' . self::OP_FIREWALLRULES . '/' . $ruleName, '',
    		Zend_Http_Client::PUT,
    		array('Content-Type' => 'application/xml; charset=utf-8'),
    		'<FirewallRule xmlns="http://schemas.microsoft.com/sqlazure/2010/12/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://schemas.microsoft.com/sqlazure/2010/12/ FirewallRule.xsd"><StartIpAddress>' . $startIpAddress . '</StartIpAddress><EndIpAddress>' . $endIpAddress . '</EndIpAddress></FirewallRule>');

            if ($response->isSuccessful()) {

    		return new Zend_Service_SqlAzure_Management_FirewallRuleInstance(
    			$ruleName,
    			$startIpAddress,
    			$endIpAddress
    		);
            } else {
		#require_once 'Zend/Service/SqlAzure/Management/Exception.php';
		throw new Zend_Service_SqlAzure_Management_Exception($this->_getErrorMessage($response, 'Resource could not be accessed.'));
            }
	}

	/**
	 * The Get Server Firewall Rules operation retrieves a list of all the firewall rules for a SQL Azure server that belongs to a subscription.
	 *
	 * @param string $serverName Server name.
	 * @return Array of Zend_Service_SqlAzure_Management_FirewallRuleInstance.
	 * @throws Zend_Service_SqlAzure_Management_Exception
	 */
	public function listFirewallRules($serverName)
	{
            if ($serverName == '' || is_null($serverName)) {
		#require_once 'Zend/Service/SqlAzure/Management/Exception.php';
                throw new Zend_Service_SqlAzure_Management_Exception('Server name should be specified.');
            }

	    $response = $this->_performRequest(self::OP_SERVERS . '/' . $serverName . '/' . self::OP_FIREWALLRULES);

            if ($response->isSuccessful()) {
		$xml = $this->_parseResponse($response);
		$xmlServices = null;

    		if (!$xml->FirewallRule) {
                    return array();
		}
		if (count($xml->FirewallRule) > 1) {
    		    $xmlServices = $xml->FirewallRule;
    		} else {
    		    $xmlServices = array($xml->FirewallRule);
    		}

		$services = array();
		if (!is_null($xmlServices)) {

                    for ($i = 0; $i < count($xmlServices); $i++) {
                        $services[] = new Zend_Service_SqlAzure_Management_FirewallRuleInstance(
					    (string)$xmlServices[$i]->Name,
					    (string)$xmlServices[$i]->StartIpAddress,
					    (string)$xmlServices[$i]->EndIpAddress
					);
                    }
		}
		return $services;
            } else {
		#require_once 'Zend/Service/SqlAzure/Management/Exception.php';
		throw new Zend_Service_SqlAzure_Management_Exception($this->_getErrorMessage($response, 'Resource could not be accessed.'));
            }
	}

	/**
	 * The Delete Server Firewall Rule operation deletes a firewall rule from a SQL Azure server that belongs to a subscription.
	 *
	 * @param string $serverName Server name.
	 * @param string $ruleName Rule name.
	 * @throws Zend_Service_SqlAzure_Management_Exception
	 */
	public function deleteFirewallRule($serverName, $ruleName)
	{
		if ($serverName == '' || is_null($serverName)) {
			#require_once 'Zend/Service/SqlAzure/Management/Exception.php';
    		throw new Zend_Service_SqlAzure_Management_Exception('Server name should be specified.');
    	}
		if ($ruleName == '' || is_null($ruleName)) {
			#require_once 'Zend/Service/SqlAzure/Management/Exception.php';
    		throw new Zend_Service_SqlAzure_Management_Exception('Rule name should be specified.');
    	}

        $response = $this->_performRequest(self::OP_SERVERS . '/' . $serverName . '/' . self::OP_FIREWALLRULES . '/' . $ruleName, '',
    		Zend_Http_Client::DELETE);

    	if (!$response->isSuccessful()) {
			#require_once 'Zend/Service/SqlAzure/Management/Exception.php';
			throw new Zend_Service_SqlAzure_Management_Exception($this->_getErrorMessage($response, 'Resource could not be accessed.'));
		}
	}

	/**
	 * Creates a firewall rule for Microsoft Services. This is required if access to SQL Azure is required from other services like Windows Azure.
	 *
	 * @param string $serverName Server name.
	 * @param boolean $allowAccess Allow access from other Microsoft Services?
	 * @throws Zend_Service_SqlAzure_Management_Exception
	 */
	public function createFirewallRuleForMicrosoftServices($serverName, $allowAccess)
	{
		if ($allowAccess) {
			$this->createFirewallRule($serverName, 'MicrosoftServices', '0.0.0.0', '0.0.0.0');
		} else {
			$this->deleteFirewallRule($serverName, 'MicrosoftServices');
		}
	}

}
