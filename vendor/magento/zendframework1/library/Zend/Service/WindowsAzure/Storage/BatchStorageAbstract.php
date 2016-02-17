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
 * @subpackage Storage
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_Service_WindowsAzure_Storage
 */
#require_once 'Zend/Service/WindowsAzure/Storage.php';

/**
 * @category   Zend
 * @package    Zend_Service_WindowsAzure
 * @subpackage Storage
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_Service_WindowsAzure_Storage_BatchStorageAbstract
    extends Zend_Service_WindowsAzure_Storage
{
    /**
     * Current batch
     *
     * @var Zend_Service_WindowsAzure_Storage_Batch
     */
    protected $_currentBatch = null;

    /**
     * Set current batch
     *
     * @param Zend_Service_WindowsAzure_Storage_Batch $batch Current batch
     * @throws Zend_Service_WindowsAzure_Exception
     */
    public function setCurrentBatch(Zend_Service_WindowsAzure_Storage_Batch $batch = null)
    {
        if (!is_null($batch) && $this->isInBatch()) {
			#require_once 'Zend/Service/WindowsAzure/Exception.php';
            throw new Zend_Service_WindowsAzure_Exception('Only one batch can be active at a time.');
        }
        $this->_currentBatch = $batch;
    }

    /**
     * Get current batch
     *
     * @return Zend_Service_WindowsAzure_Storage_Batch
     */
    public function getCurrentBatch()
    {
        return $this->_currentBatch;
    }

    /**
     * Is there a current batch?
     *
     * @return boolean
     */
    public function isInBatch()
    {
        return !is_null($this->_currentBatch);
    }

    /**
     * Starts a new batch operation set
     *
     * @return Zend_Service_WindowsAzure_Storage_Batch
     * @throws Zend_Service_WindowsAzure_Exception
     */
    public function startBatch()
    {
		#require_once 'Zend/Service/WindowsAzure/Storage/Batch.php';
        return new Zend_Service_WindowsAzure_Storage_Batch($this, $this->getBaseUrl());
    }

	/**
	 * Perform batch using Zend_Http_Client channel, combining all batch operations into one request
	 *
	 * @param array $operations Operations in batch
	 * @param boolean $forTableStorage Is the request for table storage?
	 * @param boolean $isSingleSelect Is the request a single select statement?
	 * @param string $resourceType Resource type
	 * @param string $requiredPermission Required permission
	 * @return Zend_Http_Response
	 */
	public function performBatch($operations = array(), $forTableStorage = false, $isSingleSelect = false, $resourceType = Zend_Service_WindowsAzure_Storage::RESOURCE_UNKNOWN, $requiredPermission = Zend_Service_WindowsAzure_Credentials_CredentialsAbstract::PERMISSION_READ)
	{
	    // Generate boundaries
	    $batchBoundary = 'batch_' . md5(time() . microtime());
	    $changesetBoundary = 'changeset_' . md5(time() . microtime());

	    // Set headers
	    $headers = array();

		// Add version header
		$headers['x-ms-version'] = $this->_apiVersion;

		// Add dataservice headers
		$headers['DataServiceVersion'] = '1.0;NetFx';
		$headers['MaxDataServiceVersion'] = '1.0;NetFx';

		// Add content-type header
		$headers['Content-Type'] = 'multipart/mixed; boundary=' . $batchBoundary;

		// Set path and query string
		$path           = '/$batch';
		$queryString    = '';

		// Set verb
		$httpVerb = Zend_Http_Client::POST;

		// Generate raw data
    	$rawData = '';

		// Single select?
		if ($isSingleSelect) {
		    $operation = $operations[0];
		    $rawData .= '--' . $batchBoundary . "\n";
            $rawData .= 'Content-Type: application/http' . "\n";
            $rawData .= 'Content-Transfer-Encoding: binary' . "\n\n";
            $rawData .= $operation;
            $rawData .= '--' . $batchBoundary . '--';
		} else {
    		$rawData .= '--' . $batchBoundary . "\n";
    		$rawData .= 'Content-Type: multipart/mixed; boundary=' . $changesetBoundary . "\n\n";

        		// Add operations
        		foreach ($operations as $operation)
        		{
                    $rawData .= '--' . $changesetBoundary . "\n";
                	$rawData .= 'Content-Type: application/http' . "\n";
                	$rawData .= 'Content-Transfer-Encoding: binary' . "\n\n";
                	$rawData .= $operation;
        		}
        		$rawData .= '--' . $changesetBoundary . '--' . "\n";

    		$rawData .= '--' . $batchBoundary . '--';
		}

		// Generate URL and sign request
		$requestUrl     = $this->_credentials->signRequestUrl($this->getBaseUrl() . $path . $queryString, $resourceType, $requiredPermission);
		$requestHeaders = $this->_credentials->signRequestHeaders($httpVerb, $path, $queryString, $headers, $forTableStorage, $resourceType, $requiredPermission);

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

		return $response;
	}
}
