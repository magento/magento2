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
 * @see Zend_Service_WindowsAzure_Management_OperationStatusInstance
 */
#require_once 'Zend/Service/WindowsAzure/Management/OperationStatusInstance.php';

/**
 * @see Zend_Service_WindowsAzure_Management_SubscriptionOperationInstance
 */
#require_once 'Zend/Service/WindowsAzure/Management/SubscriptionOperationInstance.php';

/**
 * @see Zend_Service_WindowsAzure_Management_DeploymentInstance
 */
#require_once 'Zend/Service/WindowsAzure/Management/DeploymentInstance.php';

/**
 * @see Zend_Service_WindowsAzure_Storage_Blob
 */
#require_once 'Zend/Service/WindowsAzure/Storage/Blob.php';

/**
 * @see Zend_Service_WindowsAzure_Storage_Table
 */
#require_once 'Zend/Service/WindowsAzure/Storage/Table.php';

/**
 * @see Zend_Service_WindowsAzure_Management_HostedServiceInstance
 */
#require_once 'Zend/Service/WindowsAzure/Management/HostedServiceInstance.php';

/**
 * @see Zend_Service_WindowsAzure_Management_CertificateInstance
 */
#require_once 'Zend/Service/WindowsAzure/Management/CertificateInstance.php';

/**
 * @see Zend_Service_WindowsAzure_Management_AffinityGroupInstance
 */
#require_once 'Zend/Service/WindowsAzure/Management/AffinityGroupInstance.php';

/**
 * @see Zend_Service_WindowsAzure_Management_LocationInstance
 */
#require_once 'Zend/Service/WindowsAzure/Management/LocationInstance.php';

/**
 * @see Zend_Service_WindowsAzure_Management_OperatingSystemInstance
 */
#require_once 'Zend/Service/WindowsAzure/Management/OperatingSystemInstance.php';

/**
 * @see Zend_Service_WindowsAzure_Management_OperatingSystemFamilyInstance
 */
#require_once 'Zend/Service/WindowsAzure/Management/OperatingSystemFamilyInstance.php';

/** @see Zend_Xml_Security */
#require_once 'Zend/Xml/Security.php';

/**
 * @category   Zend
 * @package    Zend_Service_WindowsAzure
 * @subpackage Management
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_WindowsAzure_Management_Client
{
	/**
	 * Management service URL
	 */
	const URL_MANAGEMENT        = "https://management.core.windows.net";

	/**
	 * Operations
	 */
	const OP_OPERATIONS                = "operations";
	const OP_STORAGE_ACCOUNTS          = "services/storageservices";
	const OP_HOSTED_SERVICES           = "services/hostedservices";
	const OP_AFFINITYGROUPS            = "affinitygroups";
	const OP_LOCATIONS                 = "locations";
	const OP_OPERATINGSYSTEMS          = "operatingsystems";
	const OP_OPERATINGSYSTEMFAMILIES   = "operatingsystemfamilies";

	/**
	 * Current API version
	 *
	 * @var string
	 */
	protected $_apiVersion = '2011-02-25';

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
	 * Creates a new Zend_Service_WindowsAzure_Management instance
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
			#require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Response should not be null.');
		}

        $xml = Zend_Xml_Security::scan($response->getBody());

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
     * The Get Operation Status operation returns the status of the specified operation.
     * After calling an asynchronous operation, you can call Get Operation Status to
     * determine whether the operation has succeed, failed, or is still in progress.
     *
     * @param string $requestId The request ID. If omitted, the last request ID will be used.
     * @return Zend_Service_WindowsAzure_Management_OperationStatusInstance
     * @throws Zend_Service_WindowsAzure_Management_Exception
     */
    public function getOperationStatus($requestId = '')
    {
    	if ($requestId == '') {
    		$requestId = $this->getLastRequestId();
    	}

    	$response = $this->_performRequest(self::OP_OPERATIONS . '/' . $requestId);

    	if ($response->isSuccessful()) {
			$result = $this->_parseResponse($response);

			if (!is_null($result)) {
				return new Zend_Service_WindowsAzure_Management_OperationStatusInstance(
					(string)$result->ID,
					(string)$result->Status,
					($result->Error ? (string)$result->Error->Code : ''),
					($result->Error ? (string)$result->Error->Message : '')
				);
			}
			return null;
		} else {
			#require_once 'Zend/Service/Management/Exception.php';
			throw new Zend_Service_WindowsAzure_Management_Exception($this->_getErrorMessage($response, 'Resource could not be accessed.'));
		}
    }



    /**
     * The List Subscription Operations operation returns a list of create, update,
     * and delete operations that were performed on a subscription during the specified timeframe.
     * Documentation on the parameters can be found at http://msdn.microsoft.com/en-us/library/gg715318.aspx.
     *
     * @param string $startTime The start of the timeframe to begin listing subscription operations in UTC format. This parameter and the $endTime parameter indicate the timeframe to retrieve subscription operations. This parameter cannot indicate a start date of more than 90 days in the past.
     * @param string $endTime The end of the timeframe to begin listing subscription operations in UTC format. This parameter and the $startTime parameter indicate the timeframe to retrieve subscription operations.
     * @param string $objectIdFilter Returns subscription operations only for the specified object type and object ID.
     * @param string $operationResultFilter Returns subscription operations only for the specified result status, either Succeeded, Failed, or InProgress.
     * @param string $continuationToken Internal usage.
     * @return array Array of Zend_Service_WindowsAzure_Management_SubscriptionOperationInstance
     * @throws Zend_Service_WindowsAzure_Management_Exception
     */
    public function listSubscriptionOperations($startTime, $endTime, $objectIdFilter = null, $operationResultFilter = null, $continuationToken = null)
    {
    	if ($startTime == '' || is_null($startTime)) {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
    		throw new Zend_Service_WindowsAzure_Management_Exception('Start time should be specified.');
    	}
    	if ($endTime == '' || is_null($endTime)) {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
    		throw new Zend_Service_WindowsAzure_Management_Exception('End time should be specified.');
    	}
    	if ($operationResultFilter != '' && !is_null($operationResultFilter)) {
	        $operationResultFilter = strtolower($operationResultFilter);
	    	if ($operationResultFilter != 'succeeded' && $operationResultFilter != 'failed' && $operationResultFilter != 'inprogress') {
				#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
	    		throw new Zend_Service_WindowsAzure_Management_Exception('OperationResultFilter should be succeeded|failed|inprogress.');
	    	}
    	}

    	$parameters = array();
    	$parameters[] = 'StartTime=' . $startTime;
    	$parameters[] = 'EndTime=' . $endTime;
    	if ($objectIdFilter != '' && !is_null($objectIdFilter)) {
    		$parameters[] = 'ObjectIdFilter=' . $objectIdFilter;
    	}
    	if ($operationResultFilter != '' && !is_null($operationResultFilter)) {
    		$parameters[] = 'OperationResultFilter=' . ucfirst($operationResultFilter);
    	}
    	if ($continuationToken != '' && !is_null($continuationToken)) {
    		$parameters[] = 'ContinuationToken=' . $continuationToken;
    	}

    	$response = $this->_performRequest(self::OP_OPERATIONS, '?' . implode('&', $parameters));

    	if ($response->isSuccessful()) {
			$result = $this->_parseResponse($response);
			$namespaces = $result->getDocNamespaces();
    		$result->registerXPathNamespace('__empty_ns', $namespaces['']);

			$xmlOperations = $result->xpath('//__empty_ns:SubscriptionOperation');

		    // Create return value
		    $returnValue = array();
		    foreach ($xmlOperations as $xmlOperation) {
		    	// Create operation instance
		    	$operation = new Zend_Service_WindowsAzure_Management_SubscriptionOperationInstance(
		    		$xmlOperation->OperationId,
		    		$xmlOperation->OperationObjectId,
		    		$xmlOperation->OperationName,
		    		array(),
		    		(array)$xmlOperation->OperationCaller,
		    		(array)$xmlOperation->OperationStatus
		    	);

		    	// Parse parameters
		    	$xmlOperation->registerXPathNamespace('__empty_ns', $namespaces['']);
		    	$xmlParameters = $xmlOperation->xpath('.//__empty_ns:OperationParameter');
		    	foreach ($xmlParameters as $xmlParameter) {
		    		$xmlParameterDetails = $xmlParameter->children('http://schemas.datacontract.org/2004/07/Microsoft.Samples.WindowsAzure.ServiceManagement');
		    		$operation->addOperationParameter((string)$xmlParameterDetails->Name, (string)$xmlParameterDetails->Value);
		    	}

    		    // Add to result
    		    $returnValue[] = $operation;
		    }

			// More data?
		    if (!is_null($result->ContinuationToken) && $result->ContinuationToken != '') {
		    	$returnValue = array_merge($returnValue, $this->listSubscriptionOperations($startTime, $endTime, $objectIdFilter, $operationResultFilter, (string)$result->ContinuationToken));
		    }

		    // Return
		    return $returnValue;
		} else {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
			throw new Zend_Service_WindowsAzure_Management_Exception($this->_getErrorMessage($response, 'Resource could not be accessed.'));
		}
    }

    /**
     * Wait for an operation to complete
     *
     * @param string $requestId The request ID. If omitted, the last request ID will be used.
     * @param int $sleepInterval Sleep interval in milliseconds.
     * @return Zend_Service_WindowsAzure_Management_OperationStatusInstance
     * @throws Zend_Service_WindowsAzure_Management_Exception
     */
    public function waitForOperation($requestId = '', $sleepInterval = 250)
    {
    	if ($requestId == '') {
    		$requestId = $this->getLastRequestId();
    	}
    	if ($requestId == '' || is_null($requestId)) {
    		return null;
    	}

		$status = $this->getOperationStatus($requestId);
		while ($status->Status == 'InProgress') {
		  $status = $this->getOperationStatus($requestId);
		  usleep($sleepInterval);
		}

		return $status;
    }

	/**
	 * Creates a new Zend_Service_WindowsAzure_Storage_Blob instance for the current account
	 *
	 * @param string $serviceName the service name to create a storage client for.
	 * @param Zend_Service_WindowsAzure_RetryPolicy_RetryPolicyAbstract $retryPolicy Retry policy to use when making requests
	 * @return Zend_Service_WindowsAzure_Storage_Blob
	 */
	public function createBlobClientForService($serviceName, Zend_Service_WindowsAzure_RetryPolicy_RetryPolicyAbstract $retryPolicy = null)
	{
		if ($serviceName == '' || is_null($serviceName)) {
    		throw new Zend_Service_WindowsAzure_Management_Exception('Service name should be specified.');
    	}

    	$storageKeys = $this->getStorageAccountKeys($serviceName);



		return new Zend_Service_WindowsAzure_Storage_Blob(
			Zend_Service_WindowsAzure_Storage::URL_CLOUD_BLOB,
			$serviceName,
			$storageKeys[0],
			false,
			$retryPolicy
		);
	}

	/**
	 * Creates a new Zend_Service_WindowsAzure_Storage_Table instance for the current account
	 *
	 * @param string $serviceName the service name to create a storage client for.
	 * @param Zend_Service_WindowsAzure_RetryPolicy_RetryPolicyAbstract $retryPolicy Retry policy to use when making requests
	 * @return Zend_Service_WindowsAzure_Storage_Table
	 */
	public function createTableClientForService($serviceName, Zend_Service_WindowsAzure_RetryPolicy_RetryPolicyAbstract $retryPolicy = null)
	{
		if ($serviceName == '' || is_null($serviceName)) {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
    		throw new Zend_Service_WindowsAzure_Management_Exception('Service name should be specified.');
    	}

    	$storageKeys = $this->getStorageAccountKeys($serviceName);

		return new Zend_Service_WindowsAzure_Storage_Table(
			Zend_Service_WindowsAzure_Storage::URL_CLOUD_TABLE,
			$serviceName,
			$storageKeys[0],
			false,
			$retryPolicy
		);
	}

	/**
	 * Creates a new Zend_Service_WindowsAzure_Storage_Queue instance for the current account
	 *
	 * @param string $serviceName the service name to create a storage client for.
	 * @param Zend_Service_WindowsAzure_RetryPolicy_RetryPolicyAbstract $retryPolicy Retry policy to use when making requests
	 * @return Zend_Service_WindowsAzure_Storage_Queue
	 */
	public function createQueueClientForService($serviceName, Zend_Service_WindowsAzure_RetryPolicy_RetryPolicyAbstract $retryPolicy = null)
	{
		if ($serviceName == '' || is_null($serviceName)) {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
    		throw new Zend_Service_WindowsAzure_Management_Exception('Service name should be specified.');
    	}

    	$storageKeys = $this->getStorageAccountKeys($serviceName);

		#require_once 'Zend/Service/WindowsAzure/Storage/Queue.php';

		return new Zend_Service_WindowsAzure_Storage_Queue(
			Zend_Service_WindowsAzure_Storage::URL_CLOUD_QUEUE,
			$serviceName,
			$storageKeys[0],
			false,
			$retryPolicy
		);
	}

    /**
     * The List Storage Accounts operation lists the storage accounts available under
     * the current subscription.
     *
     * @return array An array of Zend_Service_WindowsAzure_Management_StorageServiceInstance
     */
    public function listStorageAccounts()
    {
    	$response = $this->_performRequest(self::OP_STORAGE_ACCOUNTS);

    	if ($response->isSuccessful()) {
			$result = $this->_parseResponse($response);

    		if (!$result->StorageService) {
				return array();
			}
		    if (count($result->StorageService) > 1) {
    		    $xmlServices = $result->StorageService;
    		} else {
    		    $xmlServices = array($result->StorageService);
    		}

			$services = array();
			if (!is_null($xmlServices)) {
				for ($i = 0; $i < count($xmlServices); $i++) {
					$services[] = new Zend_Service_WindowsAzure_Management_StorageServiceInstance(
					    (string)$xmlServices[$i]->Url,
					    (string)$xmlServices[$i]->ServiceName
					);
				}
			}
			return $services;
		} else {
			throw new Zend_Service_WindowsAzure_Management_Exception($this->_getErrorMessage($response, 'Resource could not be accessed.'));
		}
    }

    /**
     * The Get Storage Account Properties operation returns the system properties for the
     * specified storage account. These properties include: the address, description, and
     * label of the storage account; and the name of the affinity group to which the service
     * belongs, or its geo-location if it is not part of an affinity group.
     *
     * @param string $serviceName The name of your service.
     * @return Zend_Service_WindowsAzure_Management_StorageServiceInstance
     * @throws Zend_Service_WindowsAzure_Management_Exception
     */
    public function getStorageAccountProperties($serviceName)
    {
    	if ($serviceName == '' || is_null($serviceName)) {
    		throw new Zend_Service_WindowsAzure_Management_Exception('Service name should be specified.');
    	}

    	$response = $this->_performRequest(self::OP_STORAGE_ACCOUNTS . '/' . $serviceName);

    	if ($response->isSuccessful()) {
			$xmlService = $this->_parseResponse($response);

			if (!is_null($xmlService)) {
				#require_once 'Zend/Service/WindowsAzure/Management/StorageServiceInstance.php';

				return new Zend_Service_WindowsAzure_Management_StorageServiceInstance(
					(string)$xmlService->Url,
					(string)$xmlService->ServiceName,
					(string)$xmlService->StorageServiceProperties->Description,
					(string)$xmlService->StorageServiceProperties->AffinityGroup,
					(string)$xmlService->StorageServiceProperties->Location,
					(string)$xmlService->StorageServiceProperties->Label
				);
			}
			return null;
		} else {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
			throw new Zend_Service_WindowsAzure_Management_Exception($this->_getErrorMessage($response, 'Resource could not be accessed.'));
		}
    }

    /**
     * The Get Storage Keys operation returns the primary
     * and secondary access keys for the specified storage account.
     *
     * @param string $serviceName The name of your service.
     * @return array An array of strings
     * @throws Zend_Service_WindowsAzure_Management_Exception
     */
    public function getStorageAccountKeys($serviceName)
    {
    	if ($serviceName == '' || is_null($serviceName)) {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
    		throw new Zend_Service_WindowsAzure_Management_Exception('Service name should be specified.');
    	}

    	$response = $this->_performRequest(self::OP_STORAGE_ACCOUNTS . '/' . $serviceName . '/keys');

    	if ($response->isSuccessful()) {
			$xmlService = $this->_parseResponse($response);

			if (!is_null($xmlService)) {
				return array(
					(string)$xmlService->StorageServiceKeys->Primary,
					(string)$xmlService->StorageServiceKeys->Secondary
				);
			}
			return array();
		} else {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
			throw new Zend_Service_WindowsAzure_Management_Exception($this->_getErrorMessage($response, 'Resource could not be accessed.'));
		}
    }

    /**
     * The Regenerate Keys operation regenerates the primary
     * or secondary access key for the specified storage account.
     *
     * @param string $serviceName The name of your service.
     * @param string $key		  The key to regenerate (primary or secondary)
     * @return array An array of strings
     * @throws Zend_Service_WindowsAzure_Management_Exception
     */
    public function regenerateStorageAccountKey($serviceName, $key = 'primary')
    {
    	if ($serviceName == '' || is_null($serviceName)) {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
    		throw new Zend_Service_WindowsAzure_Management_Exception('Service name should be specified.');
    	}
    	$key = strtolower($key);
    	if ($key != 'primary' && $key != 'secondary') {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
    		throw new Zend_Service_WindowsAzure_Management_Exception('Key identifier should be primary|secondary.');
    	}

    	$response = $this->_performRequest(
    		self::OP_STORAGE_ACCOUNTS . '/' . $serviceName . '/keys', '?action=regenerate',
    		Zend_Http_Client::POST,
    		array('Content-Type' => 'application/xml'),
    		'<?xml version="1.0" encoding="utf-8"?>
             <RegenerateKeys xmlns="http://schemas.microsoft.com/windowsazure">
               <KeyType>' . ucfirst($key) . '</KeyType>
             </RegenerateKeys>');

    	if ($response->isSuccessful()) {
			$xmlService = $this->_parseResponse($response);

			if (!is_null($xmlService)) {
				return array(
					(string)$xmlService->StorageServiceKeys->Primary,
					(string)$xmlService->StorageServiceKeys->Secondary
				);
			}
			return array();
		} else {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
			throw new Zend_Service_WindowsAzure_Management_Exception($this->_getErrorMessage($response, 'Resource could not be accessed.'));
		}
    }

    /**
     * The List Hosted Services operation lists the hosted services available
     * under the current subscription.
     *
     * @return array An array of Zend_Service_WindowsAzure_Management_HostedServiceInstance
     * @throws Zend_Service_WindowsAzure_Management_Exception
     */
    public function listHostedServices()
    {
    	$response = $this->_performRequest(self::OP_HOSTED_SERVICES);

    	if ($response->isSuccessful()) {
			$result = $this->_parseResponse($response);

    		if (!$result->HostedService) {
				return array();
			}
		    if (count($result->HostedService) > 1) {
    		    $xmlServices = $result->HostedService;
    		} else {
    		    $xmlServices = array($result->HostedService);
    		}

			$services = array();
			if (!is_null($xmlServices)) {

				for ($i = 0; $i < count($xmlServices); $i++) {
					$services[] = new Zend_Service_WindowsAzure_Management_HostedServiceInstance(
					    (string)$xmlServices[$i]->Url,
					    (string)$xmlServices[$i]->ServiceName
					);
				}
			}
			return $services;
		} else {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
			throw new Zend_Service_WindowsAzure_Management_Exception($this->_getErrorMessage($response, 'Resource could not be accessed.'));
		}
    }

    /**
     * The Create Hosted Service operation creates a new hosted service in Windows Azure.
     *
     * @param string $serviceName A name for the hosted service that is unique to the subscription.
     * @param string $label A label for the hosted service. The label may be up to 100 characters in length.
     * @param string $description A description for the hosted service. The description may be up to 1024 characters in length.
     * @param string $location Required if AffinityGroup is not specified. The location where the hosted service will be created.
     * @param string $affinityGroup Required if Location is not specified. The name of an existing affinity group associated with this subscription.
     */
    public function createHostedService($serviceName, $label, $description = '', $location = null, $affinityGroup = null)
    {
    	if ($serviceName == '' || is_null($serviceName)) {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
    		throw new Zend_Service_WindowsAzure_Management_Exception('Service name should be specified.');
    	}
    	if ($label == '' || is_null($label)) {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
    		throw new Zend_Service_WindowsAzure_Management_Exception('Label should be specified.');
    	}
        if (strlen($label) > 100) {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
    		throw new Zend_Service_WindowsAzure_Management_Exception('Label is too long. The maximum length is 100 characters.');
    	}
        if (strlen($description) > 1024) {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
    		throw new Zend_Service_WindowsAzure_Management_Exception('Description is too long. The maximum length is 1024 characters.');
    	}
    	if ( (is_null($location) && is_null($affinityGroup)) || (!is_null($location) && !is_null($affinityGroup)) ) {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
    		throw new Zend_Service_WindowsAzure_Management_Exception('Please specify a location -or- an affinity group for the service.');
    	}

    	$locationOrAffinityGroup = is_null($location)
    		? '<AffinityGroup>' . $affinityGroup . '</AffinityGroup>'
    		: '<Location>' . $location . '</Location>';

        $response = $this->_performRequest(self::OP_HOSTED_SERVICES, '',
    		Zend_Http_Client::POST,
    		array('Content-Type' => 'application/xml; charset=utf-8'),
    		'<CreateHostedService xmlns="http://schemas.microsoft.com/windowsazure"><ServiceName>' . $serviceName . '</ServiceName><Label>' . base64_encode($label) . '</Label><Description>' . $description . '</Description>' . $locationOrAffinityGroup . '</CreateHostedService>');

    	if (!$response->isSuccessful()) {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
			throw new Zend_Service_WindowsAzure_Management_Exception($this->_getErrorMessage($response, 'Resource could not be accessed.'));
		}
    }

    /**
     * The Update Hosted Service operation updates the label and/or the description for a hosted service in Windows Azure.
     *
     * @param string $serviceName A name for the hosted service that is unique to the subscription.
     * @param string $label A label for the hosted service. The label may be up to 100 characters in length.
     * @param string $description A description for the hosted service. The description may be up to 1024 characters in length.
     */
    public function updateHostedService($serviceName, $label, $description = '')
    {
    	if ($serviceName == '' || is_null($serviceName)) {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
    		throw new Zend_Service_WindowsAzure_Management_Exception('Service name should be specified.');
    	}
    	if ($label == '' || is_null($label)) {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
    		throw new Zend_Service_WindowsAzure_Management_Exception('Label should be specified.');
    	}
        if (strlen($label) > 100) {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
    		throw new Zend_Service_WindowsAzure_Management_Exception('Label is too long. The maximum length is 100 characters.');
    	}

        $response = $this->_performRequest(self::OP_HOSTED_SERVICES . '/' . $serviceName, '',
    		Zend_Http_Client::PUT,
    		array('Content-Type' => 'application/xml; charset=utf-8'),
    		'<UpdateHostedService xmlns="http://schemas.microsoft.com/windowsazure"><Label>' . base64_encode($label) . '</Label><Description>' . $description . '</Description></UpdateHostedService>');

    	if (!$response->isSuccessful()) {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
			throw new Zend_Service_WindowsAzure_Management_Exception($this->_getErrorMessage($response, 'Resource could not be accessed.'));
		}
    }

    /**
     * The Delete Hosted Service operation deletes the specified hosted service in Windows Azure.
     *
     * @param string $serviceName A name for the hosted service that is unique to the subscription.
     */
    public function deleteHostedService($serviceName)
    {
    	if ($serviceName == '' || is_null($serviceName)) {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
    		throw new Zend_Service_WindowsAzure_Management_Exception('Service name should be specified.');
    	}

        $response = $this->_performRequest(self::OP_HOSTED_SERVICES . '/' . $serviceName, '', Zend_Http_Client::DELETE);

    	if (!$response->isSuccessful()) {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
			throw new Zend_Service_WindowsAzure_Management_Exception($this->_getErrorMessage($response, 'Resource could not be accessed.'));
		}
    }

    /**
     * The Get Hosted Service Properties operation retrieves system properties
     * for the specified hosted service. These properties include the service
     * name and service type; the name of the affinity group to which the service
     * belongs, or its location if it is not part of an affinity group; and
     * optionally, information on the service's deployments.
     *
     * @param string $serviceName The name of your service.
     * @return Zend_Service_WindowsAzure_Management_HostedServiceInstance
     * @throws Zend_Service_WindowsAzure_Management_Exception
     */
    public function getHostedServiceProperties($serviceName)
    {
    	if ($serviceName == '' || is_null($serviceName)) {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
    		throw new Zend_Service_WindowsAzure_Management_Exception('Service name should be specified.');
    	}

    	$response = $this->_performRequest(self::OP_HOSTED_SERVICES . '/' . $serviceName, '?embed-detail=true');

    	if ($response->isSuccessful()) {
			$xmlService = $this->_parseResponse($response);

			if (!is_null($xmlService)) {

				$returnValue = new Zend_Service_WindowsAzure_Management_HostedServiceInstance(
					(string)$xmlService->Url,
					(string)$xmlService->ServiceName,
					(string)$xmlService->HostedServiceProperties->Description,
					(string)$xmlService->HostedServiceProperties->AffinityGroup,
					(string)$xmlService->HostedServiceProperties->Location,
					(string)$xmlService->HostedServiceProperties->Label
				);

				// Deployments
		    	if (count($xmlService->Deployments->Deployment) > 1) {
    		    	$xmlServices = $xmlService->Deployments->Deployment;
    			} else {
    		    	$xmlServices = array($xmlService->Deployments->Deployment);
    			}

    			$deployments = array();
    			foreach ($xmlServices as $xmlDeployment) {
					$deployments[] = $this->_convertXmlElementToDeploymentInstance($xmlDeployment);
    			}
				$returnValue->Deployments = $deployments;

				return $returnValue;
			}
			return null;
		} else {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
			throw new Zend_Service_WindowsAzure_Management_Exception($this->_getErrorMessage($response, 'Resource could not be accessed.'));
		}
    }

    /**
     * The Create Deployment operation uploads a new service package
     * and creates a new deployment on staging or production.
     *
     * @param string $serviceName		The service name
     * @param string $deploymentSlot	The deployment slot (production or staging)
	 * @param string $name              The name for the deployment. The deployment ID as listed on the Windows Azure management portal must be unique among other deployments for the hosted service.
	 * @param string $label             A URL that refers to the location of the service package in the Blob service. The service package must be located in a storage account beneath the same subscription.
	 * @param string $packageUrl        The service configuration file for the deployment.
	 * @param string $configuration     A label for this deployment, up to 100 characters in length.
	 * @param boolean $startDeployment  Indicates whether to start the deployment immediately after it is created.
	 * @param boolean $treatWarningsAsErrors Indicates whether to treat package validation warnings as errors.
     * @throws Zend_Service_WindowsAzure_Management_Exception
     */
    public function createDeployment($serviceName, $deploymentSlot, $name, $label, $packageUrl, $configuration, $startDeployment = false, $treatWarningsAsErrors = false)
    {
        if ($serviceName == '' || is_null($serviceName)) {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
    		throw new Zend_Service_WindowsAzure_Management_Exception('Service name should be specified.');
    	}
    	$deploymentSlot = strtolower($deploymentSlot);
    	if ($deploymentSlot != 'production' && $deploymentSlot != 'staging') {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
    		throw new Zend_Service_WindowsAzure_Management_Exception('Deployment slot should be production|staging.');
    	}
    	if ($name == '' || is_null($name)) {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
    		throw new Zend_Service_WindowsAzure_Management_Exception('Name should be specified.');
    	}
    	if ($label == '' || is_null($label)) {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
    		throw new Zend_Service_WindowsAzure_Management_Exception('Label should be specified.');
    	}
        if (strlen($label) > 100) {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
    		throw new Zend_Service_WindowsAzure_Management_Exception('Label is too long. The maximum length is 100 characters.');
    	}
    	if ($packageUrl == '' || is_null($packageUrl)) {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
    		throw new Zend_Service_WindowsAzure_Management_Exception('Package URL should be specified.');
    	}
    	if ($configuration == '' || is_null($configuration)) {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
    		throw new Zend_Service_WindowsAzure_Management_Exception('Configuration should be specified.');
    	}

    	if (@file_exists($configuration)) {
    		$configuration = utf8_decode(file_get_contents($configuration));
    	}

    	// Clean up the configuration
    	$conformingConfiguration = $this->_cleanConfiguration($configuration);

    	$operationUrl = self::OP_HOSTED_SERVICES . '/' . $serviceName . '/deploymentslots/' . $deploymentSlot;
        $response = $this->_performRequest($operationUrl, '',
    		Zend_Http_Client::POST,
    		array('Content-Type' => 'application/xml; charset=utf-8'),
    		'<CreateDeployment xmlns="http://schemas.microsoft.com/windowsazure"><Name>' . $name . '</Name><PackageUrl>' . $packageUrl . '</PackageUrl><Label>' . base64_encode($label) . '</Label><Configuration>' . base64_encode($conformingConfiguration) . '</Configuration><StartDeployment>' . ($startDeployment ? 'true' : 'false') . '</StartDeployment><TreatWarningsAsError>' . ($treatWarningsAsErrors ? 'true' : 'false') . '</TreatWarningsAsError></CreateDeployment>');

    	if (!$response->isSuccessful()) {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
			throw new Zend_Service_WindowsAzure_Management_Exception($this->_getErrorMessage($response, 'Resource could not be accessed.'));
		}
    }

    /**
     * The Get Deployment operation returns configuration information, status,
     * and system properties for the specified deployment.
     *
     * @param string $serviceName		The service name
     * @param string $deploymentSlot	The deployment slot (production or staging)
     * @return Zend_Service_WindowsAzure_Management_DeploymentInstance
     * @throws Zend_Service_WindowsAzure_Management_Exception
     */
    public function getDeploymentBySlot($serviceName, $deploymentSlot)
    {
        if ($serviceName == '' || is_null($serviceName)) {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
    		throw new Zend_Service_WindowsAzure_Management_Exception('Service name should be specified.');
    	}
    	$deploymentSlot = strtolower($deploymentSlot);
    	if ($deploymentSlot != 'production' && $deploymentSlot != 'staging') {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
    		throw new Zend_Service_WindowsAzure_Management_Exception('Deployment slot should be production|staging.');
    	}

    	$operationUrl = self::OP_HOSTED_SERVICES . '/' . $serviceName . '/deploymentslots/' . $deploymentSlot;
    	return $this->_getDeployment($operationUrl);
    }

    /**
     * The Get Deployment operation returns configuration information, status,
     * and system properties for the specified deployment.
     *
     * @param string $serviceName		The service name
     * @param string $deploymentId	The deployment ID as listed on the Windows Azure management portal
     * @return Zend_Service_WindowsAzure_Management_DeploymentInstance
     * @throws Zend_Service_WindowsAzure_Management_Exception
     */
    public function getDeploymentByDeploymentId($serviceName, $deploymentId)
    {
        if ($serviceName == '' || is_null($serviceName)) {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
    		throw new Zend_Service_WindowsAzure_Management_Exception('Service name should be specified.');
    	}
        if ($deploymentId == '' || is_null($deploymentId)) {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
    		throw new Zend_Service_WindowsAzure_Management_Exception('Deployment ID should be specified.');
    	}

    	$operationUrl = self::OP_HOSTED_SERVICES . '/' . $serviceName . '/deployments/' . $deploymentId;
    	return $this->_getDeployment($operationUrl);
    }

    /**
     * The Get Deployment operation returns configuration information, status,
     * and system properties for the specified deployment.
     *
     * @param string $operationUrl		The operation url
     * @return Zend_Service_WindowsAzure_Management_DeploymentInstance
     * @throws Zend_Service_WindowsAzure_Management_Exception
     */
    protected function _getDeployment($operationUrl)
    {
        $response = $this->_performRequest($operationUrl);

    	if ($response->isSuccessful()) {
			$xmlService = $this->_parseResponse($response);

			return $this->_convertXmlElementToDeploymentInstance($xmlService);
		} else {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
			throw new Zend_Service_WindowsAzure_Management_Exception($this->_getErrorMessage($response, 'Resource could not be accessed.'));
		}
    }

    /**
     * The Swap Deployment operation initiates a virtual IP swap between
     * the staging and production deployment environments for a service.
     * If the service is currently running in the staging environment,
     * it will be swapped to the production environment. If it is running
     * in the production environment, it will be swapped to staging.
     *
     * @param string $serviceName The service name.
     * @param string $productionDeploymentName The name of the production deployment.
     * @param string $sourceDeploymentName The name of the source deployment.
     * @throws Zend_Service_WindowsAzure_Management_Exception
     */
    public function swapDeployment($serviceName, $productionDeploymentName, $sourceDeploymentName)
    {
    	if ($serviceName == '' || is_null($serviceName)) {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
    		throw new Zend_Service_WindowsAzure_Management_Exception('Service name should be specified.');
    	}
    	if ($productionDeploymentName == '' || is_null($productionDeploymentName)) {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
    		throw new Zend_Service_WindowsAzure_Management_Exception('Production Deployment ID should be specified.');
    	}
    	if ($sourceDeploymentName == '' || is_null($sourceDeploymentName)) {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
    		throw new Zend_Service_WindowsAzure_Management_Exception('Source Deployment ID should be specified.');
    	}

    	$operationUrl = self::OP_HOSTED_SERVICES . '/' . $serviceName;
        $response = $this->_performRequest($operationUrl, '',
    		Zend_Http_Client::POST,
    		array('Content-Type' => 'application/xml; charset=utf-8'),
    		'<Swap xmlns="http://schemas.microsoft.com/windowsazure"><Production>' . $productionDeploymentName . '</Production><SourceDeployment>' . $sourceDeploymentName . '</SourceDeployment></Swap>');

    	if (!$response->isSuccessful()) {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
			throw new Zend_Service_WindowsAzure_Management_Exception($this->_getErrorMessage($response, 'Resource could not be accessed.'));
		}
    }

    /**
     * The Delete Deployment operation deletes the specified deployment.
     *
     * @param string $serviceName		The service name
     * @param string $deploymentSlot	The deployment slot (production or staging)
     * @throws Zend_Service_WindowsAzure_Management_Exception
     */
    public function deleteDeploymentBySlot($serviceName, $deploymentSlot)
    {
        if ($serviceName == '' || is_null($serviceName)) {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
    		throw new Zend_Service_WindowsAzure_Management_Exception('Service name should be specified.');
    	}
    	$deploymentSlot = strtolower($deploymentSlot);
    	if ($deploymentSlot != 'production' && $deploymentSlot != 'staging') {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
    		throw new Zend_Service_WindowsAzure_Management_Exception('Deployment slot should be production|staging.');
    	}

    	$operationUrl = self::OP_HOSTED_SERVICES . '/' . $serviceName . '/deploymentslots/' . $deploymentSlot;
    	return $this->_deleteDeployment($operationUrl);
    }

    /**
     * The Delete Deployment operation deletes the specified deployment.
     *
     * @param string $serviceName		The service name
     * @param string $deploymentId	The deployment ID as listed on the Windows Azure management portal
     * @throws Zend_Service_WindowsAzure_Management_Exception
     */
    public function deleteDeploymentByDeploymentId($serviceName, $deploymentId)
    {
        if ($serviceName == '' || is_null($serviceName)) {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
    		throw new Zend_Service_WindowsAzure_Management_Exception('Service name should be specified.');
    	}
    	if ($deploymentId == '' || is_null($deploymentId)) {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
    		throw new Zend_Service_WindowsAzure_Management_Exception('Deployment ID should be specified.');
    	}

    	$operationUrl = self::OP_HOSTED_SERVICES . '/' . $serviceName . '/deployments/' . $deploymentId;
    	return $this->_deleteDeployment($operationUrl);
    }

    /**
     * The Delete Deployment operation deletes the specified deployment.
     *
     * @param string $operationUrl		The operation url
     * @throws Zend_Service_WindowsAzure_Management_Exception
     */
    protected function _deleteDeployment($operationUrl)
    {
        $response = $this->_performRequest($operationUrl, '', Zend_Http_Client::DELETE);

    	if (!$response->isSuccessful()) {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
			throw new Zend_Service_WindowsAzure_Management_Exception($this->_getErrorMessage($response, 'Resource could not be accessed.'));
		}
    }

    /**
     * The Update Deployment Status operation initiates a change in deployment status.
     *
     * @param string $serviceName		The service name
     * @param string $deploymentSlot	The deployment slot (production or staging)
     * @param string $status            The deployment status (running|suspended)
     * @throws Zend_Service_WindowsAzure_Management_Exception
     */
    public function updateDeploymentStatusBySlot($serviceName, $deploymentSlot, $status = 'running')
    {
        if ($serviceName == '' || is_null($serviceName)) {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
    		throw new Zend_Service_WindowsAzure_Management_Exception('Service name should be specified.');
    	}
    	$deploymentSlot = strtolower($deploymentSlot);
    	if ($deploymentSlot != 'production' && $deploymentSlot != 'staging') {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
    		throw new Zend_Service_WindowsAzure_Management_Exception('Deployment slot should be production|staging.');
    	}
    	$status = strtolower($status);
    	if ($status != 'running' && $status != 'suspended') {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
    		throw new Zend_Service_WindowsAzure_Management_Exception('Status should be running|suspended.');
    	}

    	$operationUrl = self::OP_HOSTED_SERVICES . '/' . $serviceName . '/deploymentslots/' . $deploymentSlot;
    	return $this->_updateDeploymentStatus($operationUrl, $status);
    }

    /**
     * The Update Deployment Status operation initiates a change in deployment status.
     *
     * @param string $serviceName		The service name
     * @param string $deploymentId	The deployment ID as listed on the Windows Azure management portal
     * @param string $status            The deployment status (running|suspended)
     * @throws Zend_Service_WindowsAzure_Management_Exception
     */
    public function updateDeploymentStatusByDeploymentId($serviceName, $deploymentId, $status = 'running')
    {
        if ($serviceName == '' || is_null($serviceName)) {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
    		throw new Zend_Service_WindowsAzure_Management_Exception('Service name should be specified.');
    	}
    	if ($deploymentId == '' || is_null($deploymentId)) {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
    		throw new Zend_Service_WindowsAzure_Management_Exception('Deployment ID should be specified.');
    	}
        $status = strtolower($status);
    	if ($status != 'running' && $status != 'suspended') {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
    		throw new Zend_Service_WindowsAzure_Management_Exception('Status should be running|suspended.');
    	}

    	$operationUrl = self::OP_HOSTED_SERVICES . '/' . $serviceName . '/deployments/' . $deploymentId;
    	return $this->_updateDeploymentStatus($operationUrl, $status);
    }

    /**
     * The Update Deployment Status operation initiates a change in deployment status.
     *
     * @param string $operationUrl		The operation url
     * @param string $status            The deployment status (running|suspended)
     * @throws Zend_Service_WindowsAzure_Management_Exception
     */
    protected function _updateDeploymentStatus($operationUrl, $status = 'running')
    {
        $response = $this->_performRequest($operationUrl . '/', '?comp=status',
    		Zend_Http_Client::POST,
    		array('Content-Type' => 'application/xml; charset=utf-8'),
    		'<UpdateDeploymentStatus xmlns="http://schemas.microsoft.com/windowsazure"><Status>' . ucfirst($status) . '</Status></UpdateDeploymentStatus>');

    	if (!$response->isSuccessful()) {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
			throw new Zend_Service_WindowsAzure_Management_Exception($this->_getErrorMessage($response, 'Resource could not be accessed.'));
		}
    }

    /**
     * Converts an XmlElement into a Zend_Service_WindowsAzure_Management_DeploymentInstance
     *
     * @param object $xmlService The XML Element
     * @return Zend_Service_WindowsAzure_Management_DeploymentInstance
     * @throws Zend_Service_WindowsAzure_Management_Exception
     */
    protected function _convertXmlElementToDeploymentInstance($xmlService)
    {
		if (!is_null($xmlService)) {

			$returnValue = new Zend_Service_WindowsAzure_Management_DeploymentInstance(
				(string)$xmlService->Name,
				(string)$xmlService->DeploymentSlot,
				(string)$xmlService->PrivateID,
				(string)$xmlService->Label,
				(string)$xmlService->Url,
				(string)$xmlService->Configuration,
				(string)$xmlService->Status,
				(string)$xmlService->UpgradeStatus,
				(string)$xmlService->UpgradeType,
				(string)$xmlService->CurrentUpgradeDomainState,
				(string)$xmlService->CurrentUpgradeDomain,
				(string)$xmlService->UpgradeDomainCount
			);

			// Append role instances
			if ($xmlService->RoleInstanceList && $xmlService->RoleInstanceList->RoleInstance) {
				$xmlRoleInstances = $xmlService->RoleInstanceList->RoleInstance;
				if (count($xmlService->RoleInstanceList->RoleInstance) == 1) {
		    	    $xmlRoleInstances = array($xmlService->RoleInstanceList->RoleInstance);
		    	}

				$roleInstances = array();
				if (!is_null($xmlRoleInstances)) {
					for ($i = 0; $i < count($xmlRoleInstances); $i++) {
						$roleInstances[] = array(
						    'rolename' => (string)$xmlRoleInstances[$i]->RoleName,
						    'instancename' => (string)$xmlRoleInstances[$i]->InstanceName,
						    'instancestatus' => (string)$xmlRoleInstances[$i]->InstanceStatus
						);
					}
				}

				$returnValue->RoleInstanceList = $roleInstances;
			}

			// Append roles
			if ($xmlService->RoleList && $xmlService->RoleList->Role) {
				$xmlRoles = $xmlService->RoleList->Role;
				if (count($xmlService->RoleList->Role) == 1) {
		    	    $xmlRoles = array($xmlService->RoleList->Role);
		    	}

				$roles = array();
				if (!is_null($xmlRoles)) {
					for ($i = 0; $i < count($xmlRoles); $i++) {
						$roles[] = array(
						    'rolename' => (string)$xmlRoles[$i]->RoleName,
						    'osversion' => (!is_null($xmlRoles[$i]->OsVersion) ? (string)$xmlRoles[$i]->OsVersion : (string)$xmlRoles[$i]->OperatingSystemVersion)
						);
					}
				}
				$returnValue->RoleList = $roles;
			}

			return $returnValue;
		}
		return null;
    }

    /**
     * Updates a deployment's role instance count.
     *
     * @param string $serviceName		The service name
     * @param string $deploymentSlot	The deployment slot (production or staging)
     * @param string|array $roleName	The role name
     * @param string|array $instanceCount The instance count
     * @throws Zend_Service_WindowsAzure_Management_Exception
     */
	public function setInstanceCountBySlot($serviceName, $deploymentSlot, $roleName, $instanceCount) {
	    if ($serviceName == '' || is_null($serviceName)) {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
    		throw new Zend_Service_WindowsAzure_Management_Exception('Service name should be specified.');
    	}
    	$deploymentSlot = strtolower($deploymentSlot);
    	if ($deploymentSlot != 'production' && $deploymentSlot != 'staging') {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
    		throw new Zend_Service_WindowsAzure_Management_Exception('Deployment slot should be production|staging.');
    	}
    	if ($roleName == '' || is_null($roleName)) {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
    		throw new Zend_Service_WindowsAzure_Management_Exception('Role name name should be specified.');
    	}

		// Get configuration
		$deployment = $this->getDeploymentBySlot($serviceName, $deploymentSlot);
		$configuration = $deployment->Configuration;
		$configuration = $this->_updateInstanceCountInConfiguration($roleName, $instanceCount, $configuration);

		// Update configuration
		$this->configureDeploymentBySlot($serviceName, $deploymentSlot, $configuration);
	}

    /**
     * Updates a deployment's role instance count.
     *
     * @param string $serviceName		The service name
     * @param string $deploymentSlot	The deployment slot (production or staging)
     * @param string|array $roleName	The role name
     * @param string|array $instanceCount The instance count
     * @throws Zend_Service_WindowsAzure_Management_Exception
     */
    public function setInstanceCountByDeploymentId($serviceName, $deploymentId, $roleName, $instanceCount)
    {
	    if ($serviceName == '' || is_null($serviceName)) {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
    		throw new Zend_Service_WindowsAzure_Management_Exception('Service name should be specified.');
    	}
        if ($deploymentId == '' || is_null($deploymentId)) {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
    		throw new Zend_Service_WindowsAzure_Management_Exception('Deployment ID should be specified.');
    	}
    	if ($roleName == '' || is_null($roleName)) {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
    		throw new Zend_Service_WindowsAzure_Management_Exception('Role name name should be specified.');
    	}

		// Get configuration
		$deployment = $this->getDeploymentByDeploymentId($serviceName, $deploymentId);
		$configuration = $deployment->Configuration;
		$configuration = $this->_updateInstanceCountInConfiguration($roleName, $instanceCount, $configuration);

		// Update configuration
		$this->configureDeploymentByDeploymentId($serviceName, $deploymentId, $configuration);
    }

    /**
     * Updates instance count in configuration XML.
     *
     * @param string|array $roleName			The role name
     * @param string|array $instanceCount		The instance count
     * @param string $configuration             XML configuration represented as a string
     * @throws Zend_Service_WindowsAzure_Management_Exception
     */
	protected function _updateInstanceCountInConfiguration($roleName, $instanceCount, $configuration) {
    	// Change variables
		if (!is_array($roleName)) {
			$roleName = array($roleName);
		}
		if (!is_array($instanceCount)) {
			$instanceCount = array($instanceCount);
		}

		$configuration = preg_replace('/(<\?xml[^?]+?)utf-16/i', '$1utf-8', $configuration);
		//$configuration = '<?xml version="1.0">' . substr($configuration, strpos($configuration, '>') + 2);

		$xml = Zend_Xml_Security::scan($configuration);

		// http://www.php.net/manual/en/simplexmlelement.xpath.php#97818
		$namespaces = $xml->getDocNamespaces();
	    $xml->registerXPathNamespace('__empty_ns', $namespaces['']);

		for ($i = 0; $i < count($roleName); $i++) {
			$elements = $xml->xpath('//__empty_ns:Role[@name="' . $roleName[$i] . '"]/__empty_ns:Instances');

			if (count($elements) == 1) {
				$element = $elements[0];
				$element['count'] = $instanceCount[$i];
			}
		}

		$configuration = $xml->asXML();
		//$configuration = preg_replace('/(<\?xml[^?]+?)utf-8/i', '$1utf-16', $configuration);

		return $configuration;
	}

    /**
     * The Change Deployment Configuration request may be specified as follows.
     * Note that you can change a deployment's configuration either by specifying the deployment
     * environment (staging or production), or by specifying the deployment's unique name.
     *
     * @param string $serviceName		The service name
     * @param string $deploymentSlot	The deployment slot (production or staging)
     * @param string $configuration     XML configuration represented as a string
     * @throws Zend_Service_WindowsAzure_Management_Exception
     */
    public function configureDeploymentBySlot($serviceName, $deploymentSlot, $configuration)
    {
        if ($serviceName == '' || is_null($serviceName)) {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
    		throw new Zend_Service_WindowsAzure_Management_Exception('Service name should be specified.');
    	}
    	$deploymentSlot = strtolower($deploymentSlot);
    	if ($deploymentSlot != 'production' && $deploymentSlot != 'staging') {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
    		throw new Zend_Service_WindowsAzure_Management_Exception('Deployment slot should be production|staging.');
    	}
    	if ($configuration == '' || is_null($configuration)) {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
    		throw new Zend_Service_WindowsAzure_Management_Exception('Configuration name should be specified.');
    	}

        if (@file_exists($configuration)) {
    		$configuration = utf8_decode(file_get_contents($configuration));
    	}

    	$operationUrl = self::OP_HOSTED_SERVICES . '/' . $serviceName . '/deploymentslots/' . $deploymentSlot;
    	return $this->_configureDeployment($operationUrl, $configuration);
    }

    /**
     * The Change Deployment Configuration request may be specified as follows.
     * Note that you can change a deployment's configuration either by specifying the deployment
     * environment (staging or production), or by specifying the deployment's unique name.
     *
     * @param string $serviceName		The service name
     * @param string $deploymentId	The deployment ID as listed on the Windows Azure management portal
     * @param string $configuration     XML configuration represented as a string
     * @throws Zend_Service_WindowsAzure_Management_Exception
     */
    public function configureDeploymentByDeploymentId($serviceName, $deploymentId, $configuration)
    {
        if ($serviceName == '' || is_null($serviceName)) {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
    		throw new Zend_Service_WindowsAzure_Management_Exception('Service name should be specified.');
    	}
    	if ($deploymentId == '' || is_null($deploymentId)) {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
    		throw new Zend_Service_WindowsAzure_Management_Exception('Deployment ID should be specified.');
    	}
    	if ($configuration == '' || is_null($configuration)) {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
    		throw new Zend_Service_WindowsAzure_Management_Exception('Configuration name should be specified.');
    	}

        if (@file_exists($configuration)) {
    		$configuration = utf8_decode(file_get_contents($configuration));
    	}

    	$operationUrl = self::OP_HOSTED_SERVICES . '/' . $serviceName . '/deployments/' . $deploymentId;
    	return $this->_configureDeployment($operationUrl, $configuration);
    }

    /**
     * The Change Deployment Configuration request may be specified as follows.
     * Note that you can change a deployment's configuration either by specifying the deployment
     * environment (staging or production), or by specifying the deployment's unique name.
     *
     * @param string $operationUrl		The operation url
     * @param string $configuration     XML configuration represented as a string
     * @throws Zend_Service_WindowsAzure_Management_Exception
     */
    protected function _configureDeployment($operationUrl, $configuration)
    {
    	// Clean up the configuration
    	$conformingConfiguration = $this->_cleanConfiguration($configuration);

        $response = $this->_performRequest($operationUrl . '/', '?comp=config',
    		Zend_Http_Client::POST,
    		array('Content-Type' => 'application/xml; charset=utf-8'),
    		'<ChangeConfiguration xmlns="http://schemas.microsoft.com/windowsazure" xmlns:i="http://www.w3.org/2001/XMLSchema-instance"><Configuration>' . base64_encode($conformingConfiguration) . '</Configuration></ChangeConfiguration>');

    	if (!$response->isSuccessful()) {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
			throw new Zend_Service_WindowsAzure_Management_Exception($this->_getErrorMessage($response, 'Resource could not be accessed.'));
		}
    }

    /**
     * The Upgrade Deployment operation initiates an upgrade.
     *
     * @param string $serviceName		The service name
     * @param string $deploymentSlot	The deployment slot (production or staging)
	 * @param string $label             A URL that refers to the location of the service package in the Blob service. The service package must be located in a storage account beneath the same subscription.
	 * @param string $packageUrl        The service configuration file for the deployment.
	 * @param string $configuration     A label for this deployment, up to 100 characters in length.
     * @param string $mode              The type of upgrade to initiate. Possible values are Auto or Manual.
     * @param string $roleToUpgrade     The name of the specific role to upgrade.
     * @throws Zend_Service_WindowsAzure_Management_Exception
     */
    public function upgradeDeploymentBySlot($serviceName, $deploymentSlot, $label, $packageUrl, $configuration, $mode = 'auto', $roleToUpgrade = null)
    {
        if ($serviceName == '' || is_null($serviceName)) {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
    		throw new Zend_Service_WindowsAzure_Management_Exception('Service name should be specified.');
    	}
    	$deploymentSlot = strtolower($deploymentSlot);
    	if ($deploymentSlot != 'production' && $deploymentSlot != 'staging') {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
    		throw new Zend_Service_WindowsAzure_Management_Exception('Deployment slot should be production|staging.');
    	}
    	if ($label == '' || is_null($label)) {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
    		throw new Zend_Service_WindowsAzure_Management_Exception('Label should be specified.');
    	}
        if (strlen($label) > 100) {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
    		throw new Zend_Service_WindowsAzure_Management_Exception('Label is too long. The maximum length is 100 characters.');
    	}
    	if ($packageUrl == '' || is_null($packageUrl)) {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
    		throw new Zend_Service_WindowsAzure_Management_Exception('Package URL should be specified.');
    	}
    	if ($configuration == '' || is_null($configuration)) {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
    		throw new Zend_Service_WindowsAzure_Management_Exception('Configuration should be specified.');
    	}
    	$mode = strtolower($mode);
    	if ($mode != 'auto' && $mode != 'manual') {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
    		throw new Zend_Service_WindowsAzure_Management_Exception('Mode should be auto|manual.');
    	}

    	if (@file_exists($configuration)) {
    		$configuration = utf8_decode(file_get_contents($configuration));
    	}

		$operationUrl = self::OP_HOSTED_SERVICES . '/' . $serviceName . '/deploymentslots/' . $deploymentSlot;
    	return $this->_upgradeDeployment($operationUrl, $label, $packageUrl, $configuration, $mode, $roleToUpgrade);
    }

    /**
     * The Upgrade Deployment operation initiates an upgrade.
     *
     * @param string $serviceName		The service name
     * @param string $deploymentId	The deployment ID as listed on the Windows Azure management portal
	 * @param string $label             A URL that refers to the location of the service package in the Blob service. The service package must be located in a storage account beneath the same subscription.
	 * @param string $packageUrl        The service configuration file for the deployment.
	 * @param string $configuration     A label for this deployment, up to 100 characters in length.
     * @param string $mode              The type of upgrade to initiate. Possible values are Auto or Manual.
     * @param string $roleToUpgrade     The name of the specific role to upgrade.
     * @throws Zend_Service_WindowsAzure_Management_Exception
     */
    public function upgradeDeploymentByDeploymentId($serviceName, $deploymentId, $label, $packageUrl, $configuration, $mode = 'auto', $roleToUpgrade = null)
    {
        if ($serviceName == '' || is_null($serviceName)) {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
    		throw new Zend_Service_WindowsAzure_Management_Exception('Service name should be specified.');
    	}
    	if ($deploymentId == '' || is_null($deploymentId)) {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
    		throw new Zend_Service_WindowsAzure_Management_Exception('Deployment ID should be specified.');
    	}
    	if ($label == '' || is_null($label)) {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
    		throw new Zend_Service_WindowsAzure_Management_Exception('Label should be specified.');
    	}
        if (strlen($label) > 100) {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
    		throw new Zend_Service_WindowsAzure_Management_Exception('Label is too long. The maximum length is 100 characters.');
    	}
    	if ($packageUrl == '' || is_null($packageUrl)) {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
    		throw new Zend_Service_WindowsAzure_Management_Exception('Package URL should be specified.');
    	}
    	if ($configuration == '' || is_null($configuration)) {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
    		throw new Zend_Service_WindowsAzure_Management_Exception('Configuration should be specified.');
    	}
    	$mode = strtolower($mode);
    	if ($mode != 'auto' && $mode != 'manual') {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
    		throw new Zend_Service_WindowsAzure_Management_Exception('Mode should be auto|manual.');
    	}

    	if (@file_exists($configuration)) {
    		$configuration = utf8_decode(file_get_contents($configuration));
    	}

		$operationUrl = self::OP_HOSTED_SERVICES . '/' . $serviceName . '/deployments/' . $deploymentId;
    	return $this->_upgradeDeployment($operationUrl, $label, $packageUrl, $configuration, $mode, $roleToUpgrade);
    }


    /**
     * The Upgrade Deployment operation initiates an upgrade.
     *
     * @param string $operationUrl		The operation url
	 * @param string $label             A URL that refers to the location of the service package in the Blob service. The service package must be located in a storage account beneath the same subscription.
	 * @param string $packageUrl        The service configuration file for the deployment.
	 * @param string $configuration     A label for this deployment, up to 100 characters in length.
     * @param string $mode              The type of upgrade to initiate. Possible values are Auto or Manual.
     * @param string $roleToUpgrade     The name of the specific role to upgrade.
     * @throws Zend_Service_WindowsAzure_Management_Exception
     */
    protected function _upgradeDeployment($operationUrl, $label, $packageUrl, $configuration, $mode, $roleToUpgrade)
    {
    	// Clean up the configuration
    	$conformingConfiguration = $this->_cleanConfiguration($configuration);

        $response = $this->_performRequest($operationUrl . '/', '?comp=upgrade',
    		Zend_Http_Client::POST,
    		array('Content-Type' => 'application/xml; charset=utf-8'),
    		'<UpgradeDeployment xmlns="http://schemas.microsoft.com/windowsazure"><Mode>' . ucfirst($mode) . '</Mode><PackageUrl>' . $packageUrl . '</PackageUrl><Configuration>' . base64_encode($conformingConfiguration) . '</Configuration><Label>' . base64_encode($label) . '</Label>' . (!is_null($roleToUpgrade) ? '<RoleToUpgrade>' . $roleToUpgrade . '</RoleToUpgrade>' : '') . '</UpgradeDeployment>');

    	if (!$response->isSuccessful()) {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
			throw new Zend_Service_WindowsAzure_Management_Exception($this->_getErrorMessage($response, 'Resource could not be accessed.'));
		}
    }

    /**
     * The Walk Upgrade Domain operation specifies the next upgrade domain to be walked during an in-place upgrade.
     *
     * @param string $serviceName		The service name
     * @param string $deploymentSlot	The deployment slot (production or staging)
	 * @param int $upgradeDomain     An integer value that identifies the upgrade domain to walk. Upgrade domains are identified with a zero-based index: the first upgrade domain has an ID of 0, the second has an ID of 1, and so on.
     * @throws Zend_Service_WindowsAzure_Management_Exception
     */
    public function walkUpgradeDomainBySlot($serviceName, $deploymentSlot, $upgradeDomain = 0)
    {
        if ($serviceName == '' || is_null($serviceName)) {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
    		throw new Zend_Service_WindowsAzure_Management_Exception('Service name should be specified.');
    	}
    	$deploymentSlot = strtolower($deploymentSlot);
    	if ($deploymentSlot != 'production' && $deploymentSlot != 'staging') {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
    		throw new Zend_Service_WindowsAzure_Management_Exception('Deployment slot should be production|staging.');
    	}

		$operationUrl = self::OP_HOSTED_SERVICES . '/' . $serviceName . '/deploymentslots/' . $deploymentSlot;
    	return $this->_walkUpgradeDomain($operationUrl, $upgradeDomain);
    }

    /**
     * The Walk Upgrade Domain operation specifies the next upgrade domain to be walked during an in-place upgrade.
     *
     * @param string $serviceName		The service name
     * @param string $deploymentId	The deployment ID as listed on the Windows Azure management portal
	 * @param int $upgradeDomain     An integer value that identifies the upgrade domain to walk. Upgrade domains are identified with a zero-based index: the first upgrade domain has an ID of 0, the second has an ID of 1, and so on.
     * @throws Zend_Service_WindowsAzure_Management_Exception
     */
    public function walkUpgradeDomainByDeploymentId($serviceName, $deploymentId, $upgradeDomain = 0)
    {
        if ($serviceName == '' || is_null($serviceName)) {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
    		throw new Zend_Service_WindowsAzure_Management_Exception('Service name should be specified.');
    	}
    	if ($deploymentId == '' || is_null($deploymentId)) {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
    		throw new Zend_Service_WindowsAzure_Management_Exception('Deployment ID should be specified.');
    	}

		$operationUrl = self::OP_HOSTED_SERVICES . '/' . $serviceName . '/deployments/' . $deploymentId;
    	return $this->_walkUpgradeDomain($operationUrl, $upgradeDomain);
    }


    /**
     * The Walk Upgrade Domain operation specifies the next upgrade domain to be walked during an in-place upgrade.
     *
     * @param string $operationUrl   The operation url
	 * @param int $upgradeDomain     An integer value that identifies the upgrade domain to walk. Upgrade domains are identified with a zero-based index: the first upgrade domain has an ID of 0, the second has an ID of 1, and so on.
     * @throws Zend_Service_WindowsAzure_Management_Exception
     */
    protected function _walkUpgradeDomain($operationUrl, $upgradeDomain = 0)
    {
        $response = $this->_performRequest($operationUrl . '/', '?comp=walkupgradedomain',
    		Zend_Http_Client::POST,
    		array('Content-Type' => 'application/xml; charset=utf-8'),
    		'<WalkUpgradeDomain xmlns="http://schemas.microsoft.com/windowsazure"><UpgradeDomain>' . $upgradeDomain . '</UpgradeDomain></WalkUpgradeDomain>');

    	if (!$response->isSuccessful()) {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
			throw new Zend_Service_WindowsAzure_Management_Exception($this->_getErrorMessage($response, 'Resource could not be accessed.'));
		}
    }

    /**
     * The Reboot Role Instance operation requests a reboot of a role instance
     * that is running in a deployment.
     *
     * @param string $serviceName		The service name
     * @param string $deploymentSlot	The deployment slot (production or staging)
     * @param string $roleInstanceName  The role instance name
     * @throws Zend_Service_WindowsAzure_Management_Exception
     */
    public function rebootRoleInstanceBySlot($serviceName, $deploymentSlot, $roleInstanceName)
    {
        if ($serviceName == '' || is_null($serviceName)) {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
    		throw new Zend_Service_WindowsAzure_Management_Exception('Service name should be specified.');
    	}
    	$deploymentSlot = strtolower($deploymentSlot);
    	if ($deploymentSlot != 'production' && $deploymentSlot != 'staging') {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
    		throw new Zend_Service_WindowsAzure_Management_Exception('Deployment slot should be production|staging.');
    	}
        if ($roleInstanceName == '' || is_null($roleInstanceName)) {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
    		throw new Zend_Service_WindowsAzure_Management_Exception('Role instance name should be specified.');
    	}

    	$operationUrl = self::OP_HOSTED_SERVICES . '/' . $serviceName . '/deploymentslots/' . $deploymentSlot . '/roleinstances/' . $roleInstanceName;
    	return $this->_rebootOrReimageRoleInstance($operationUrl, 'reboot');
    }

    /**
     * The Reboot Role Instance operation requests a reboot of a role instance
     * that is running in a deployment.
     *
     * @param string $serviceName		The service name
     * @param string $deploymentId	The deployment ID as listed on the Windows Azure management portal
     * @param string $roleInstanceName  The role instance name
     * @throws Zend_Service_WindowsAzure_Management_Exception
     */
    public function rebootRoleInstanceByDeploymentId($serviceName, $deploymentId, $roleInstanceName)
    {
        if ($serviceName == '' || is_null($serviceName)) {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
    		throw new Zend_Service_WindowsAzure_Management_Exception('Service name should be specified.');
    	}
    	if ($deploymentId == '' || is_null($deploymentId)) {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
    		throw new Zend_Service_WindowsAzure_Management_Exception('Deployment ID should be specified.');
    	}
        if ($roleInstanceName == '' || is_null($roleInstanceName)) {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
    		throw new Zend_Service_WindowsAzure_Management_Exception('Role instance name should be specified.');
    	}

    	$operationUrl = self::OP_HOSTED_SERVICES . '/' . $serviceName . '/deployments/' . $deploymentId . '/roleinstances/' . $roleInstanceName;
    	return $this->_rebootOrReimageRoleInstance($operationUrl, 'reboot');
    }

    /**
     * The Reimage Role Instance operation requests a reimage of a role instance
     * that is running in a deployment.
     *
     * @param string $serviceName		The service name
     * @param string $deploymentSlot	The deployment slot (production or staging)
     * @param string $roleInstanceName  The role instance name
     * @throws Zend_Service_WindowsAzure_Management_Exception
     */
    public function reimageRoleInstanceBySlot($serviceName, $deploymentSlot, $roleInstanceName)
    {
        if ($serviceName == '' || is_null($serviceName)) {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
    		throw new Zend_Service_WindowsAzure_Management_Exception('Service name should be specified.');
    	}
    	$deploymentSlot = strtolower($deploymentSlot);
    	if ($deploymentSlot != 'production' && $deploymentSlot != 'staging') {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
    		throw new Zend_Service_WindowsAzure_Management_Exception('Deployment slot should be production|staging.');
    	}
        if ($roleInstanceName == '' || is_null($roleInstanceName)) {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
    		throw new Zend_Service_WindowsAzure_Management_Exception('Role instance name should be specified.');
    	}

    	$operationUrl = self::OP_HOSTED_SERVICES . '/' . $serviceName . '/deploymentslots/' . $deploymentSlot . '/roleinstances/' . $roleInstanceName;
    	return $this->_rebootOrReimageRoleInstance($operationUrl, 'reimage');
    }

    /**
     * The Reimage Role Instance operation requests a reimage of a role instance
     * that is running in a deployment.
     *
     * @param string $serviceName		The service name
     * @param string $deploymentId	    The deployment ID as listed on the Windows Azure management portal
     * @param string $roleInstanceName  The role instance name
     * @throws Zend_Service_WindowsAzure_Management_Exception
     */
    public function reimageRoleInstanceByDeploymentId($serviceName, $deploymentId, $roleInstanceName)
    {
        if ($serviceName == '' || is_null($serviceName)) {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
    		throw new Zend_Service_WindowsAzure_Management_Exception('Service name should be specified.');
    	}
    	if ($deploymentId == '' || is_null($deploymentId)) {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
    		throw new Zend_Service_WindowsAzure_Management_Exception('Deployment ID should be specified.');
    	}
        if ($roleInstanceName == '' || is_null($roleInstanceName)) {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
    		throw new Zend_Service_WindowsAzure_Management_Exception('Role instance name should be specified.');
    	}

    	$operationUrl = self::OP_HOSTED_SERVICES . '/' . $serviceName . '/deployments/' . $deploymentId . '/roleinstances/' . $roleInstanceName;
    	return $this->_rebootOrReimageRoleInstance($operationUrl, 'reimage');
    }

    /**
     * Reboots or reimages a role instance.
     *
     * @param string $operationUrl		The operation url
     * @param string $operation The operation (reboot|reimage)
     * @throws Zend_Service_WindowsAzure_Management_Exception
     */
    protected function _rebootOrReimageRoleInstance($operationUrl, $operation = 'reboot')
    {
        $response = $this->_performRequest($operationUrl, '?comp=' . $operation, Zend_Http_Client::POST);

    	if (!$response->isSuccessful()) {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
			throw new Zend_Service_WindowsAzure_Management_Exception($this->_getErrorMessage($response, 'Resource could not be accessed.'));
		}
    }

    /**
     * The List Certificates operation lists all certificates associated with
     * the specified hosted service.
     *
     * @param string $serviceName		The service name
     * @return array Array of Zend_Service_WindowsAzure_Management_CertificateInstance
     * @throws Zend_Service_WindowsAzure_Management_Exception
     */
    public function listCertificates($serviceName)
    {
        if ($serviceName == '' || is_null($serviceName)) {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
    		throw new Zend_Service_WindowsAzure_Management_Exception('Service name should be specified.');
    	}

    	$operationUrl = self::OP_HOSTED_SERVICES . '/' . $serviceName . '/certificates';
        $response = $this->_performRequest($operationUrl);

    	if ($response->isSuccessful()) {
			$result = $this->_parseResponse($response);

			if (!$result->Certificate) {
				return array();
			}
		    if (count($result->Certificate) > 1) {
    		    $xmlServices = $result->Certificate;
    		} else {
    		    $xmlServices = array($result->Certificate);
    		}

			$services = array();
			if (!is_null($xmlServices)) {

				for ($i = 0; $i < count($xmlServices); $i++) {
					$services[] = new Zend_Service_WindowsAzure_Management_CertificateInstance(
					    (string)$xmlServices[$i]->CertificateUrl,
					    (string)$xmlServices[$i]->Thumbprint,
					    (string)$xmlServices[$i]->ThumbprintAlgorithm,
					    (string)$xmlServices[$i]->Data
					);
				}
			}
			return $services;
		} else {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
			throw new Zend_Service_WindowsAzure_Management_Exception($this->_getErrorMessage($response, 'Resource could not be accessed.'));
		}
    }

    /**
     * The Get Certificate operation returns the public data for the specified certificate.
     *
     * @param string $serviceName|$certificateUrl	The service name -or- the certificate URL
     * @param string $algorithm         			Algorithm
     * @param string $thumbprint        			Thumbprint
     * @return Zend_Service_WindowsAzure_Management_CertificateInstance
     * @throws Zend_Service_WindowsAzure_Management_Exception
     */
    public function getCertificate($serviceName, $algorithm = '', $thumbprint = '')
    {
        if ($serviceName == '' || is_null($serviceName)) {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
    		throw new Zend_Service_WindowsAzure_Management_Exception('Service name or certificate URL should be specified.');
    	}
    	if (strpos($serviceName, 'https') === false && ($algorithm == '' || is_null($algorithm)) && ($thumbprint == '' || is_null($thumbprint))) {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
    		throw new Zend_Service_WindowsAzure_Management_Exception('Algorithm and thumbprint should be specified.');
    	}

    	$operationUrl = str_replace($this->getBaseUrl(), '', $serviceName);
    	if (strpos($serviceName, 'https') === false) {
    		$operationUrl = self::OP_HOSTED_SERVICES . '/' . $serviceName . '/certificates/' . $algorithm . '-' . strtoupper($thumbprint);
    	}

        $response = $this->_performRequest($operationUrl);

    	if ($response->isSuccessful()) {
			$result = $this->_parseResponse($response);

			return new Zend_Service_WindowsAzure_Management_CertificateInstance(
				$this->getBaseUrl() . $operationUrl,
				$algorithm,
				$thumbprint,
				(string)$result->Data
			);
		} else {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
			throw new Zend_Service_WindowsAzure_Management_Exception($this->_getErrorMessage($response, 'Resource could not be accessed.'));
		}
    }

    /**
     * The Add Certificate operation adds a certificate to the subscription.
     *
     * @param string $serviceName         The service name
     * @param string $certificateData     Certificate data
     * @param string $certificatePassword The certificate password
     * @param string $certificateFormat   The certificate format. Currently, only 'pfx' is supported.
     * @throws Zend_Service_WindowsAzure_Management_Exception
     */
    public function addCertificate($serviceName, $certificateData, $certificatePassword, $certificateFormat = 'pfx')
    {
    	if ($serviceName == '' || is_null($serviceName)) {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
    		throw new Zend_Service_WindowsAzure_Management_Exception('Service name should be specified.');
    	}
    	if ($certificateData == '' || is_null($certificateData)) {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
    		throw new Zend_Service_WindowsAzure_Management_Exception('Certificate data should be specified.');
    	}
    	if ($certificatePassword == '' || is_null($certificatePassword)) {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
    		throw new Zend_Service_WindowsAzure_Management_Exception('Certificate password should be specified.');
    	}
    	if ($certificateFormat != 'pfx') {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
    		throw new Zend_Service_WindowsAzure_Management_Exception('Certificate format should be "pfx".');
    	}

    	if (@file_exists($certificateData)) {
    		$certificateData = file_get_contents($certificateData);
    	}

    	$operationUrl = self::OP_HOSTED_SERVICES . '/' . $serviceName . '/certificates';
        $response = $this->_performRequest($operationUrl, '',
    		Zend_Http_Client::POST,
    		array('Content-Type' => 'application/xml; charset=utf-8'),
    		'<CertificateFile xmlns="http://schemas.microsoft.com/windowsazure"><Data>' . base64_encode($certificateData) . '</Data><CertificateFormat>' . $certificateFormat . '</CertificateFormat><Password>' . $certificatePassword . '</Password></CertificateFile>');

    	if (!$response->isSuccessful()) {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
			throw new Zend_Service_WindowsAzure_Management_Exception($this->_getErrorMessage($response, 'Resource could not be accessed.'));
		}
    }

    /**
     * The Delete Certificate operation deletes a certificate from the subscription's certificate store.
     *
     * @param string $serviceName|$certificateUrl	The service name -or- the certificate URL
     * @param string $algorithm         			Algorithm
     * @param string $thumbprint        			Thumbprint
     * @throws Zend_Service_WindowsAzure_Management_Exception
     */
    public function deleteCertificate($serviceName, $algorithm = '', $thumbprint = '')
    {
        if ($serviceName == '' || is_null($serviceName)) {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
    		throw new Zend_Service_WindowsAzure_Management_Exception('Service name or certificate URL should be specified.');
    	}
    	if (strpos($serviceName, 'https') === false && ($algorithm == '' || is_null($algorithm)) && ($thumbprint == '' || is_null($thumbprint))) {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
    		throw new Zend_Service_WindowsAzure_Management_Exception('Algorithm and thumbprint should be specified.');
    	}

    	$operationUrl = str_replace($this->getBaseUrl(), '', $serviceName);
    	if (strpos($serviceName, 'https') === false) {
    		$operationUrl = self::OP_HOSTED_SERVICES . '/' . $serviceName . '/certificates/' . $algorithm . '-' . strtoupper($thumbprint);
    	}

        $response = $this->_performRequest($operationUrl, '', Zend_Http_Client::DELETE);

    	if (!$response->isSuccessful()) {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
			throw new Zend_Service_WindowsAzure_Management_Exception($this->_getErrorMessage($response, 'Resource could not be accessed.'));
		}
    }

    /**
     * The List Affinity Groups operation lists the affinity groups associated with
     * the specified subscription.
     *
     * @return array Array of Zend_Service_WindowsAzure_Management_AffinityGroupInstance
     * @throws Zend_Service_WindowsAzure_Management_Exception
     */
    public function listAffinityGroups()
    {
        $response = $this->_performRequest(self::OP_AFFINITYGROUPS);

    	if ($response->isSuccessful()) {
			$result = $this->_parseResponse($response);

    		if (!$result->AffinityGroup) {
				return array();
			}
		    if (count($result->AffinityGroup) > 1) {
    		    $xmlServices = $result->AffinityGroup;
    		} else {
    		    $xmlServices = array($result->AffinityGroup);
    		}

			$services = array();
			if (!is_null($xmlServices)) {

				for ($i = 0; $i < count($xmlServices); $i++) {
					$services[] = new Zend_Service_WindowsAzure_Management_AffinityGroupInstance(
					    (string)$xmlServices[$i]->Name,
					    (string)$xmlServices[$i]->Label,
					    (string)$xmlServices[$i]->Description,
					    (string)$xmlServices[$i]->Location
					);
				}
			}
			return $services;
		} else {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
			throw new Zend_Service_WindowsAzure_Management_Exception($this->_getErrorMessage($response, 'Resource could not be accessed.'));
		}
    }

    /**
     * The Create Affinity Group operation creates a new affinity group for the specified subscription.
     *
     * @param string $name A name for the affinity group that is unique to the subscription.
     * @param string $label A label for the affinity group. The label may be up to 100 characters in length.
     * @param string $description A description for the affinity group. The description may be up to 1024 characters in length.
     * @param string $location The location where the affinity group will be created. To list available locations, use the List Locations operation.
     */
    public function createAffinityGroup($name, $label, $description = '', $location = '')
    {
    	if ($name == '' || is_null($name)) {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
    		throw new Zend_Service_WindowsAzure_Management_Exception('Affinity group name should be specified.');
    	}
    	if ($label == '' || is_null($label)) {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
    		throw new Zend_Service_WindowsAzure_Management_Exception('Label should be specified.');
    	}
        if (strlen($label) > 100) {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
    		throw new Zend_Service_WindowsAzure_Management_Exception('Label is too long. The maximum length is 100 characters.');
    	}
        if (strlen($description) > 1024) {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
    		throw new Zend_Service_WindowsAzure_Management_Exception('Description is too long. The maximum length is 1024 characters.');
    	}
    	if ($location == '' || is_null($location)) {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
    		throw new Zend_Service_WindowsAzure_Management_Exception('Location should be specified.');
    	}

        $response = $this->_performRequest(self::OP_AFFINITYGROUPS, '',
    		Zend_Http_Client::POST,
    		array('Content-Type' => 'application/xml; charset=utf-8'),
    		'<CreateAffinityGroup xmlns="http://schemas.microsoft.com/windowsazure"><Name>' . $name . '</Name><Label>' . base64_encode($label) . '</Label><Description>' . $description . '</Description><Location>' . $location . '</Location></CreateAffinityGroup>');

    	if (!$response->isSuccessful()) {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
			throw new Zend_Service_WindowsAzure_Management_Exception($this->_getErrorMessage($response, 'Resource could not be accessed.'));
		}
    }

    /**
     * The Update Affinity Group operation updates the label and/or the description for an affinity group for the specified subscription.
     *
     * @param string $name The name for the affinity group that should be updated.
     * @param string $label A label for the affinity group. The label may be up to 100 characters in length.
     * @param string $description A description for the affinity group. The description may be up to 1024 characters in length.
     */
    public function updateAffinityGroup($name, $label, $description = '')
    {
    	if ($name == '' || is_null($name)) {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
    		throw new Zend_Service_WindowsAzure_Management_Exception('Affinity group name should be specified.');
    	}
    	if ($label == '' || is_null($label)) {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
    		throw new Zend_Service_WindowsAzure_Management_Exception('Label should be specified.');
    	}
        if (strlen($label) > 100) {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
    		throw new Zend_Service_WindowsAzure_Management_Exception('Label is too long. The maximum length is 100 characters.');
    	}
        if (strlen($description) > 1024) {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
    		throw new Zend_Service_WindowsAzure_Management_Exception('Description is too long. The maximum length is 1024 characters.');
    	}

        $response = $this->_performRequest(self::OP_AFFINITYGROUPS . '/' . $name, '',
    		Zend_Http_Client::PUT,
    		array('Content-Type' => 'application/xml; charset=utf-8'),
    		'<UpdateAffinityGroup xmlns="http://schemas.microsoft.com/windowsazure"><Label>' . base64_encode($label) . '</Label><Description>' . $description . '</Description></UpdateAffinityGroup>');

    	if (!$response->isSuccessful()) {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
			throw new Zend_Service_WindowsAzure_Management_Exception($this->_getErrorMessage($response, 'Resource could not be accessed.'));
		}
    }

    /**
     * The Delete Affinity Group operation deletes an affinity group in the specified subscription.
     *
     * @param string $name The name for the affinity group that should be deleted.
     */
    public function deleteAffinityGroup($name)
    {
    	if ($name == '' || is_null($name)) {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
    		throw new Zend_Service_WindowsAzure_Management_Exception('Affinity group name should be specified.');
    	}

        $response = $this->_performRequest(self::OP_AFFINITYGROUPS . '/' . $name, '',
    		Zend_Http_Client::DELETE);

    	if (!$response->isSuccessful()) {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
			throw new Zend_Service_WindowsAzure_Management_Exception($this->_getErrorMessage($response, 'Resource could not be accessed.'));
		}
    }

    /**
     * The Get Affinity Group Properties operation returns the
     * system properties associated with the specified affinity group.
     *
     * @param string $affinityGroupName The affinity group name.
     * @return Zend_Service_WindowsAzure_Management_AffinityGroupInstance
     * @throws Zend_Service_WindowsAzure_Management_Exception
     */
    public function getAffinityGroupProperties($affinityGroupName)
    {
        if ($affinityGroupName == '' || is_null($affinityGroupName)) {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
    		throw new Zend_Service_WindowsAzure_Management_Exception('Affinity group name should be specified.');
    	}

        $response = $this->_performRequest(self::OP_AFFINITYGROUPS . '/' . $affinityGroupName);

    	if ($response->isSuccessful()) {
			$result = $this->_parseResponse($response);

			$affinityGroup = new Zend_Service_WindowsAzure_Management_AffinityGroupInstance(
				$affinityGroupName,
				(string)$result->Label,
				(string)$result->Description,
				(string)$result->Location
			);

			// Hosted services
			if (count($result->HostedServices->HostedService) > 1) {
		    	$xmlService = $result->HostedServices->HostedService;
		    } else {
		    	$xmlService = array($result->HostedServices->HostedService);
		    }

			$services = array();
			if (!is_null($xmlService)) {
				for ($i = 0; $i < count($xmlService); $i++) {
					$services[] = array(
						'url' => (string)$xmlService[$i]->Url,
						'name' => (string)$xmlService[$i]->ServiceName
					);
				}
			}
			$affinityGroup->HostedServices = $services;

			// Storage services
			if (count($result->StorageServices->StorageService) > 1) {
		    	$xmlService = $result->StorageServices->StorageService;
		    } else {
		    	$xmlService = array($result->StorageServices->StorageService);
		    }

			$services = array();
			if (!is_null($xmlService)) {
				for ($i = 0; $i < count($xmlService); $i++) {
					$services[] = array(
						'url' => (string)$xmlService[$i]->Url,
						'name' => (string)$xmlService[$i]->ServiceName
					);
				}
			}
			$affinityGroup->StorageServices = $services;

			return $affinityGroup;
		} else {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
			throw new Zend_Service_WindowsAzure_Management_Exception($this->_getErrorMessage($response, 'Resource could not be accessed.'));
		}
    }

    /**
     * The List Locations operation lists all of the data center locations
     * that are valid for your subscription.
     *
     * @return array Array of Zend_Service_WindowsAzure_Management_LocationInstance
     * @throws Zend_Service_WindowsAzure_Management_Exception
     */
    public function listLocations()
    {
        $response = $this->_performRequest(self::OP_LOCATIONS);

    	if ($response->isSuccessful()) {
			$result = $this->_parseResponse($response);

    		if (!$result->Location) {
				return array();
			}
		    if (count($result->Location) > 1) {
    		    $xmlServices = $result->Location;
    		} else {
    		    $xmlServices = array($result->Location);
    		}

			$services = array();
			if (!is_null($xmlServices)) {

				for ($i = 0; $i < count($xmlServices); $i++) {
					$services[] = new Zend_Service_WindowsAzure_Management_LocationInstance(
					    (string)$xmlServices[$i]->Name
					);
				}
			}
			return $services;
		} else {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
			throw new Zend_Service_WindowsAzure_Management_Exception($this->_getErrorMessage($response, 'Resource could not be accessed.'));
		}
    }

    /**
     * The List Operating Systems operation lists the versions of the guest operating system
     * that are currently available in Windows Azure. The 2010-10-28 version of List Operating
     * Systems also indicates what family an operating system version belongs to.
     * Currently Windows Azure supports two operating system families: the Windows Azure guest
     * operating system that is substantially compatible with Windows Server 2008 SP2,
     * and the Windows Azure guest operating system that is substantially compatible with
     * Windows Server 2008 R2.
     *
     * @return array Array of Zend_Service_WindowsAzure_Management_OperatingSystemInstance
     * @throws Zend_Service_WindowsAzure_Management_Exception
     */
    public function listOperatingSystems()
    {
        $response = $this->_performRequest(self::OP_OPERATINGSYSTEMS);

    	if ($response->isSuccessful()) {
			$result = $this->_parseResponse($response);

    		if (!$result->OperatingSystem) {
				return array();
			}
		    if (count($result->OperatingSystem) > 1) {
    		    $xmlServices = $result->OperatingSystem;
    		} else {
    		    $xmlServices = array($result->OperatingSystem);
    		}

			$services = array();
			if (!is_null($xmlServices)) {

				for ($i = 0; $i < count($xmlServices); $i++) {
					$services[] = new Zend_Service_WindowsAzure_Management_OperatingSystemInstance(
					    (string)$xmlServices[$i]->Version,
					    (string)$xmlServices[$i]->Label,
					    ((string)$xmlServices[$i]->IsDefault == 'true'),
					    ((string)$xmlServices[$i]->IsActive == 'true'),
					    (string)$xmlServices[$i]->Family,
					    (string)$xmlServices[$i]->FamilyLabel
					);
				}
			}
			return $services;
		} else {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
			throw new Zend_Service_WindowsAzure_Management_Exception($this->_getErrorMessage($response, 'Resource could not be accessed.'));
		}
    }

    /**
     * The List OS Families operation lists the guest operating system families
     * available in Windows Azure, and also lists the operating system versions
     * available for each family. Currently Windows Azure supports two operating
     * system families: the Windows Azure guest operating system that is
     * substantially compatible with Windows Server 2008 SP2, and the Windows
     * Azure guest operating system that is substantially compatible with
     * Windows Server 2008 R2.
     *
     * @return array Array of Zend_Service_WindowsAzure_Management_OperatingSystemFamilyInstance
     * @throws Zend_Service_WindowsAzure_Management_Exception
     */
    public function listOperatingSystemFamilies()
    {
        $response = $this->_performRequest(self::OP_OPERATINGSYSTEMFAMILIES);

    	if ($response->isSuccessful()) {
			$result = $this->_parseResponse($response);

    		if (!$result->OperatingSystemFamily) {
				return array();
			}
		    if (count($result->OperatingSystemFamily) > 1) {
    		    $xmlServices = $result->OperatingSystemFamily;
    		} else {
    		    $xmlServices = array($result->OperatingSystemFamily);
    		}

			$services = array();
			if (!is_null($xmlServices)) {

				for ($i = 0; $i < count($xmlServices); $i++) {
					$services[] = new Zend_Service_WindowsAzure_Management_OperatingSystemFamilyInstance(
					    (string)$xmlServices[$i]->Name,
					    (string)$xmlServices[$i]->Label
					);

					if (count($xmlServices[$i]->OperatingSystems->OperatingSystem) > 1) {
		    		    $xmlOperatingSystems = $xmlServices[$i]->OperatingSystems->OperatingSystem;
		    		} else {
		    		    $xmlOperatingSystems = array($xmlServices[$i]->OperatingSystems->OperatingSystem);
		    		}

					$operatingSystems = array();
					if (!is_null($xmlOperatingSystems)) {
						#require_once 'Zend/Service/WindowsAzure/Management/OperatingSystemInstance.php';
						for ($i = 0; $i < count($xmlOperatingSystems); $i++) {
							$operatingSystems[] = new Zend_Service_WindowsAzure_Management_OperatingSystemInstance(
							    (string)$xmlOperatingSystems[$i]->Version,
							    (string)$xmlOperatingSystems[$i]->Label,
							    ((string)$xmlOperatingSystems[$i]->IsDefault == 'true'),
							    ((string)$xmlOperatingSystems[$i]->IsActive == 'true'),
							    (string)$xmlServices[$i]->Name,
							    (string)$xmlServices[$i]->Label
							);
						}
					}
					$services[ count($services) - 1 ]->OperatingSystems = $operatingSystems;
				}
			}
			return $services;
		} else {
			#require_once 'Zend/Service/WindowsAzure/Management/Exception.php';
			throw new Zend_Service_WindowsAzure_Management_Exception($this->_getErrorMessage($response, 'Resource could not be accessed.'));
		}
    }

    /**
     * Clean configuration
     *
     * @param string $configuration Configuration to clean.
     * @return string
     */
    public function _cleanConfiguration($configuration) {
    	$configuration = str_replace('?<?', '<?', $configuration);
		$configuration = str_replace("\r", "", $configuration);
		$configuration = str_replace("\n", "", $configuration);

		return $configuration;
    }
}
