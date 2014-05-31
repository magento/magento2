<?php
/**
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
 * @package    Zend_Cloud
 * @subpackage DocumentService
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

#require_once 'Zend/Cloud/DocumentService/Adapter/AbstractAdapter.php';
#require_once 'Zend/Cloud/DocumentService/Adapter/WindowsAzure/Query.php';
#require_once 'Zend/Cloud/DocumentService/Exception.php';
#require_once 'Zend/Service/WindowsAzure/Storage/DynamicTableEntity.php';
#require_once 'Zend/Service/WindowsAzure/Storage/Table.php';

/**
 * SimpleDB adapter for document service.
 *
 * @category   Zend
 * @package    Zend_Cloud
 * @subpackage DocumentService
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Cloud_DocumentService_Adapter_WindowsAzure 
    extends Zend_Cloud_DocumentService_Adapter_AbstractAdapter
{
    /*
     * Options array keys for the Azure adapter.
     */
    const ACCOUNT_NAME          = 'storage_accountname';
    const ACCOUNT_KEY           = 'storage_accountkey';
    const HOST                  = "storage_host";
    const PROXY_HOST            = "storage_proxy_host";
    const PROXY_PORT            = "storage_proxy_port";
    const PROXY_CREDENTIALS     = "storage_proxy_credentials";
    const DEFAULT_PARTITION_KEY = "default_partition_key";

    const PARTITION_KEY         = 'PartitionKey';
    const ROW_KEY               = 'RowKey';
    const VERIFY_ETAG           = "verify_etag";
    const TIMESTAMP_KEY         = "Timestamp";
    
    const DEFAULT_HOST          = Zend_Service_WindowsAzure_Storage::URL_CLOUD_TABLE;
    const DEFAULT_QUERY_CLASS   = 'Zend_Cloud_DocumentService_Adapter_WindowsAzure_Query';

    /**
     * Azure  service instance.
     * 
     * @var Zend_Service_WindowsAzure_Storage_Table
     */
    protected $_storageClient;

    /**
     * Class to utilize for new query objects
     * 
     * @var string
     */
    protected $_queryClass = 'Zend_Cloud_DocumentService_Adapter_WindowsAzure_Query';

    /**
     * Partition key to use by default when constructing document identifiers
     * @var string
     */
    protected $_defaultPartitionKey;

    /**
     * Constructor
     * 
     * @param array $options 
     * @return void
     */
    public function __construct($options = array()) 
    {
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        }

        if (empty($options)) {
            $options = array();
        }

        if (!is_array($options)) {
            throw new Zend_Cloud_DocumentService_Exception('Invalid options provided');
        }

        if (isset($options[self::DOCUMENT_CLASS])) {
            $this->setDocumentClass($options[self::DOCUMENT_CLASS]);
        }

        if (isset($options[self::DOCUMENTSET_CLASS])) {
            $this->setDocumentSetClass($options[self::DOCUMENTSET_CLASS]);
        }

        if (isset($options[self::QUERY_CLASS])) {
            $this->setQueryClass($options[self::QUERY_CLASS]);
        }

        // Build Zend_Service_WindowsAzure_Storage_Blob instance
        if (!isset($options[self::HOST])) {
            $host = self::DEFAULT_HOST;
        } else {
            $host = $options[self::HOST];
        }

        if (! isset($options[self::ACCOUNT_NAME])) {
            throw new Zend_Cloud_DocumentService_Exception('No Windows Azure account name provided.');
        }

        if (! isset($options[self::ACCOUNT_KEY])) {
            throw new Zend_Cloud_DocumentService_Exception('No Windows Azure account key provided.');
        }

        // TODO: support $usePathStyleUri and $retryPolicy
        try {
            $this->_storageClient = new Zend_Service_WindowsAzure_Storage_Table(
                    $host, $options[self::ACCOUNT_NAME], $options[self::ACCOUNT_KEY]);
            // Parse other options
            if (! empty($options[self::PROXY_HOST])) {
                $proxyHost = $options[self::PROXY_HOST];
                $proxyPort = isset($options[self::PROXY_PORT]) ? $options[self::PROXY_PORT] : 8080;
                $proxyCredentials = isset($options[self::PROXY_CREDENTIALS]) ? $options[self::PROXY_CREDENTIALS] : '';
                $this->_storageClient->setProxy(true, $proxyHost, $proxyPort, $proxyCredentials);
            }
            if (isset($options[self::HTTP_ADAPTER])) {
                $this->_storageClient->setHttpClientChannel($options[self::HTTP_ADAPTER]);
            }
        } catch(Zend_Service_WindowsAzure_Exception $e) {
            throw new Zend_Cloud_DocumentService_Exception('Error on document service creation: '.$e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Set the default partition key
     * 
     * @param  string $key 
     * @return Zend_Cloud_DocumentService_Adapter_WindowsAzure
     */
    public function setDefaultPartitionKey($key)
    {
        $this->_validateKey($key);
        $this->_defaultPartitionKey = $key;
        return $this;
    }

    /**
     * Retrieve default partition key
     * 
     * @return null|string
     */
    public function getDefaultPartitionKey()
    {
        return $this->_defaultPartitionKey;
    }

    /**
     * Create collection.
     *
     * @param  string $name
     * @param  array  $options
     * @return boolean
     */
    public function createCollection($name, $options = null) 
    {
        if (!preg_match('/^[A-Za-z][A-Za-z0-9]{2,}$/', $name)) {
            throw new Zend_Cloud_DocumentService_Exception('Invalid collection name; Windows Azure collection names must consist of alphanumeric characters only, and be at least 3 characters long');
        }
        try {
            $this->_storageClient->createTable($name);
        } catch(Zend_Service_WindowsAzure_Exception $e) {
            if (strpos($e->getMessage(), "table specified already exists") === false) {
                throw new Zend_Cloud_DocumentService_Exception('Error on collection creation: '.$e->getMessage(), $e->getCode(), $e);
            }
        }
        return true;
    }

    /**
     * Delete collection.
     *
     * @param  string $name
     * @param  array  $options
     * @return boolean
     */
    public function deleteCollection($name, $options = null) 
    {
        try {
            $this->_storageClient->deleteTable($name);
        } catch(Zend_Service_WindowsAzure_Exception $e) {
            if (strpos($e->getMessage(), "does not exist") === false) {
                throw new Zend_Cloud_DocumentService_Exception('Error on collection deletion: '.$e->getMessage(), $e->getCode(), $e);
            }
        }
        return true;
    }

    /**
     * List collections.
     *
     * @param  array  $options
     * @return array
     */
    public function listCollections($options = null) 
    {
        try {
            $tables = $this->_storageClient->listTables();
            $restables = array();
            foreach ($tables as $table) {
                $restables[] = $table->name;
            }
            return $restables;
        } catch(Zend_Service_WindowsAzure_Exception $e) {
            throw new Zend_Cloud_DocumentService_Exception('Error on collection list: '.$e->getMessage(), $e->getCode(), $e);
        }

        return $tables;
    }

    /**
     * Create suitable document from array of fields
     * 
     * @param  array $document
     * @param  null|string $collectionName Collection to which this document belongs
     * @return Zend_Cloud_DocumentService_Document
     */
    protected function _getDocumentFromArray($document, $collectionName = null)
    {
        $key = null;
        if (!isset($document[Zend_Cloud_DocumentService_Document::KEY_FIELD])) {
            if (isset($document[self::ROW_KEY])) {
                $rowKey = $document[self::ROW_KEY];
                    unset($document[self::ROW_KEY]);
                if (isset($document[self::PARTITION_KEY])) {
                    $key = array($document[self::PARTITION_KEY], $rowKey);
                    unset($document[self::PARTITION_KEY]);
                } elseif (null !== ($partitionKey = $this->getDefaultPartitionKey())) {
                    $key = array($partitionKey, $rowKey);
                } elseif (null !== $collectionName) {
                    $key = array($collectionName, $rowKey);
                }
            }
        } else {
            $key = $document[Zend_Cloud_DocumentService_Document::KEY_FIELD];
            unset($document[Zend_Cloud_DocumentService_Document::KEY_FIELD]);
        }

        $documentClass = $this->getDocumentClass();
        return new $documentClass($document, $key);
    }
    
    /**
     * List all documents in a collection
     * 
     * @param  string $collectionName 
     * @param  null|array $options 
     * @return Zend_Cloud_DocumentService_DocumentSet
     */
    public function listDocuments($collectionName, array $options = null)
    {
        $select = $this->select()->from($collectionName);
        return $this->query($collectionName, $select);
    }

    /**
     * Insert document
     *
     * @param  array|Zend_Cloud_DocumentService_Document $document
     * @param  array                         $options
     * @return boolean
     */
    public function insertDocument($collectionName, $document, $options = null)
    {
        if (is_array($document)) {
            $document =  $this->_getDocumentFromArray($document, $collectionName);
        } 
        
        if (!$document instanceof Zend_Cloud_DocumentService_Document) {
            throw new Zend_Cloud_DocumentService_Exception('Invalid document supplied');
        }
        
        $key = $this->_validateDocumentId($document->getId(), $collectionName);
        $document->setId($key);
        
        $this->_validateCompositeKey($key);
        $this->_validateFields($document);
        try {
        
            $entity = new Zend_Service_WindowsAzure_Storage_DynamicTableEntity($key[0], $key[1]);
            $entity->setAzureValues($document->getFields(), true);
            $this->_storageClient->insertEntity($collectionName, $entity);
        } catch(Zend_Service_WindowsAzure_Exception $e) {
            throw new Zend_Cloud_DocumentService_Exception('Error on document insertion: '.$e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Replace document. 
     * 
     * The new document replaces the existing document.
     *
     * @param  Zend_Cloud_DocumentService_Document $document
     * @param  array                         $options
     * @return boolean
     */
    public function replaceDocument($collectionName, $document, $options = null)
    {
        if (is_array($document)) {
            $document = $this->_getDocumentFromArray($document, $collectionName);
        } 
        
        if (!$document instanceof Zend_Cloud_DocumentService_Document) {
            throw new Zend_Cloud_DocumentService_Exception('Invalid document supplied');
        }
        
        $key = $this->_validateDocumentId($document->getId(), $collectionName);
        $this->_validateFields($document);
        try {
            $entity = new Zend_Service_WindowsAzure_Storage_DynamicTableEntity($key[0], $key[1]);
            $entity->setAzureValues($document->getFields(), true);
            if (isset($options[self::VERIFY_ETAG])) {
                $entity->setEtag($options[self::VERIFY_ETAG]);
            }
            
            $this->_storageClient->updateEntity($collectionName, $entity, isset($options[self::VERIFY_ETAG]));
        } catch(Zend_Service_WindowsAzure_Exception $e) {
            throw new Zend_Cloud_DocumentService_Exception('Error on document replace: '.$e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Update document. 
     * 
     * The new document is merged the existing document.
     *
     * @param  string $collectionName
     * @param  mixed|Zend_Cloud_DocumentService_Document $documentId Document identifier or document contaiing updates
     * @param  null|array|Zend_Cloud_DocumentService_Document Fields to update (or new fields))
     * @param  array $options
     * @return boolean
     */
    public function updateDocument($collectionName, $documentId, $fieldset = null, $options = null)
    {
        if (null === $fieldset && $documentId instanceof Zend_Cloud_DocumentService_Document) {
            $fieldset   = $documentId->getFields();
            $documentId = $documentId->getId();
        } elseif ($fieldset instanceof Zend_Cloud_DocumentService_Document) {
            if ($documentId == null) {
                $documentId = $fieldset->getId();
            }
            $fieldset = $fieldset->getFields();
        }

        $this->_validateCompositeKey($documentId, $collectionName);
        $this->_validateFields($fieldset);
        try {
            $entity = new Zend_Service_WindowsAzure_Storage_DynamicTableEntity($documentId[0], $documentId[1]);

            // Ensure timestamp is set correctly
            if (isset($fieldset[self::TIMESTAMP_KEY])) {
                $entity->setTimestamp($fieldset[self::TIMESTAMP_KEY]);
                unset($fieldset[self::TIMESTAMP_KEY]);
            }

            $entity->setAzureValues($fieldset, true);
            if (isset($options[self::VERIFY_ETAG])) {
                $entity->setEtag($options[self::VERIFY_ETAG]);
            }
            
            $this->_storageClient->mergeEntity($collectionName, $entity, isset($options[self::VERIFY_ETAG]));
        } catch(Zend_Service_WindowsAzure_Exception $e) {
            throw new Zend_Cloud_DocumentService_Exception('Error on document update: '.$e->getMessage(), $e->getCode(), $e);
        }
    }
    
    /**
     * Delete document.
     *
     * @param  mixed  $document Document ID or Document object.
     * @param  array  $options
     * @return void
     */
    public function deleteDocument($collectionName, $documentId, $options = null)
    {
        if ($documentId instanceof Zend_Cloud_DocumentService_Document) {
            $documentId = $documentId->getId();
        }

        $documentId = $this->_validateDocumentId($documentId, $collectionName);

        try {
            $entity = new Zend_Service_WindowsAzure_Storage_DynamicTableEntity($documentId[0], $documentId[1]);
            if (isset($options[self::VERIFY_ETAG])) {
                $entity->setEtag($options[self::VERIFY_ETAG]);
            }
            $this->_storageClient->deleteEntity($collectionName, $entity, isset($options[self::VERIFY_ETAG]));
        } catch(Zend_Service_WindowsAzure_Exception $e) {
            if (strpos($e->getMessage(), "does not exist") === false) {
                throw new Zend_Cloud_DocumentService_Exception('Error on document deletion: '.$e->getMessage(), $e->getCode(), $e);
            }
        }
    }

    /**
     * Fetch single document by ID
     * 
     * @param  string $collectionName Collection name
     * @param  mixed $documentId Document ID, adapter-dependent
     * @param  array $options
     * @return Zend_Cloud_DocumentService_Document
     */
    public function fetchDocument($collectionName, $documentId, $options = null)
    {
        $documentId = $this->_validateDocumentId($documentId, $collectionName);
        try {
            $entity = $this->_storageClient->retrieveEntityById($collectionName, $documentId[0], $documentId[1]);
            $documentClass = $this->getDocumentClass();
            return new $documentClass($this->_resolveAttributes($entity), array($entity->getPartitionKey(), $entity->getRowKey()));
        } catch (Zend_Service_WindowsAzure_Exception $e) {
            if (strpos($e->getMessage(), "does not exist") !== false) {
                return false;
            }
            throw new Zend_Cloud_DocumentService_Exception('Error on document fetch: '.$e->getMessage(), $e->getCode(), $e);
        }
    }
    
    /**
     * Query for documents stored in the document service. If a string is passed in
     * $query, the query string will be passed directly to the service.
     *
     * @param  string $collectionName Collection name
     * @param  string|Zend_Cloud_DocumentService_Adapter_WindowsAzure_Query $query
     * @param  array $options
     * @return array Zend_Cloud_DocumentService_DocumentSet
     */
    public function query($collectionName, $query, $options = null)
    {
        try {
            if ($query instanceof Zend_Cloud_DocumentService_Adapter_WindowsAzure_Query) {
                $entities = $this->_storageClient->retrieveEntities($query->assemble());
            } else {
                $entities = $this->_storageClient->retrieveEntities($collectionName, $query);
            }

            $documentClass = $this->getDocumentClass();
            $resultSet     = array();
            foreach ($entities as $entity) {
                $resultSet[] = new $documentClass(
                    $this->_resolveAttributes($entity),
                    array($entity->getPartitionKey(), $entity->getRowKey())
                );
            }
        } catch(Zend_Service_WindowsAzure_Exception $e) {
            throw new Zend_Cloud_DocumentService_Exception('Error on document query: '.$e->getMessage(), $e->getCode(), $e);
        }

        $setClass = $this->getDocumentSetClass();
        return new $setClass($resultSet);
    }
    
    /**
     * Create query statement
     *
     * @return Zend_Cloud_DocumentService_Query
     */
    public function select($fields = null)
    {
        $queryClass = $this->getQueryClass();
        if (!class_exists($queryClass)) {
            #require_once 'Zend/Loader.php';
            Zend_Loader::loadClass($queryClass);
        }

        $query = new $queryClass();
        $defaultClass = self::DEFAULT_QUERY_CLASS;
        if (!$query instanceof $defaultClass) {
            throw new Zend_Cloud_DocumentService_Exception('Query class must extend ' . self::DEFAULT_QUERY_CLASS);
        }

        $query->select($fields);
        return $query;        
    }
    
    /**
     * Get the concrete service client
     *
     * @return Zend_Service_WindowsAzure_Storage_Table
     */
    public function getClient()
    {
        return $this->_storageClient;
    }
    
    /**
     * Resolve table values to attributes
     * 
     * @param  Zend_Service_WindowsAzure_Storage_TableEntity $entity 
     * @return array
     */
    protected function _resolveAttributes(Zend_Service_WindowsAzure_Storage_TableEntity $entity)
    {
        $result = array();
        foreach ($entity->getAzureValues() as $attr) {
            $result[$attr->Name] = $attr->Value;
        }
        return $result;
    }
    

    /**
     * Validate a partition or row key
     * 
     * @param  string $key 
     * @return void
     * @throws Zend_Cloud_DocumentService_Exception
     */
    protected function _validateKey($key)
    {
        if (preg_match('@[/#?' . preg_quote('\\') . ']@', $key)) {
            throw new Zend_Cloud_DocumentService_Exception('Invalid partition or row key provided; must not contain /, \\,  #, or ? characters');
        }
    }

    /**
     * Validate a composite key
     * 
     * @param  array $key 
     * @return throws Zend_Cloud_DocumentService_Exception
     */
    protected function _validateCompositeKey(array $key)
    {
        if (2 != count($key)) {
            throw new Zend_Cloud_DocumentService_Exception('Invalid document key provided; must contain exactly two elements: a PartitionKey and a RowKey');
        }
        foreach ($key as $k) {
            $this->_validateKey($k);
        }
    }

    /**
     * Validate a document identifier
     *
     * If the identifier is an array containing a valid partition and row key, 
     * returns it. If the identifier is a string:
     * - if a default partition key is present, it creates an identifier using 
     *   that and the provided document ID
     * - if a collection name is provided, it will use that for the partition key
     * - otherwise, it's invalid
     * 
     * @param  array|string $documentId 
     * @param  null|string $collectionName 
     * @return array
     * @throws Zend_Cloud_DocumentService_Exception
     */
    protected function _validateDocumentId($documentId, $collectionName = false)
    {
        if (is_array($documentId)) {
            $this->_validateCompositeKey($documentId);
            return $documentId;
        }
        if (!is_string($documentId)) {
            throw new Zend_Cloud_DocumentService_Exception('Invalid document identifier; must be a string or an array');
        }

        $this->_validateKey($documentId);

        if (null !== ($partitionKey = $this->getDefaultPartitionKey())) {
            return array($partitionKey, $documentId);
        }
        if (null !== $collectionName) {
            return array($collectionName, $documentId);
        }
        throw new Zend_Cloud_DocumentService_Exception('Cannot determine partition name; invalid document identifier');
    }

    /**
     * Validate a document's fields for well-formedness
     *
     * Since Azure uses Atom, and fieldnames are included as part of XML 
     * element tag names, the field names must be valid XML names.
     *
     * @param  Zend_Cloud_DocumentService_Document|array $document
     * @return void
     * @throws Zend_Cloud_DocumentService_Exception
     */
    public function _validateFields($document)
    {
        if ($document instanceof Zend_Cloud_DocumentService_Document) {
            $document = $document->getFields();
        } elseif (!is_array($document)) {
            throw new Zend_Cloud_DocumentService_Exception('Cannot inspect fields; invalid type provided');
        }

        foreach (array_keys($document) as $key) {
            $this->_validateFieldKey($key);
        }
    }

    /**
     * Validate an individual field name for well-formedness
     *
     * Since Azure uses Atom, and fieldnames are included as part of XML 
     * element tag names, the field names must be valid XML names.
     *
     * While we could potentially normalize names, this could also lead to 
     * conflict with other field names -- which we should avoid. As such,
     * invalid field names will raise an exception.
     *
     * @param  string $key
     * @return void
     * @throws Zend_Cloud_DocumentService_Exception
     */
    public function _validateFieldKey($key)
    {
        if (!preg_match('/^[_A-Za-z][-._A-Za-z0-9]*$/', $key)) {
            throw new Zend_Cloud_DocumentService_Exception('Field keys must conform to XML names (^[_A-Za-z][-._A-Za-z0-9]*$); key "' . $key . '" does not match');
        }
    }
}
