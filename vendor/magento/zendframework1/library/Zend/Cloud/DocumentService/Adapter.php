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
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * Common interface for document storage services in the cloud. This interface
 * supports most document services and provides some flexibility for
 * vendor-specific features and requirements via an optional $options array in
 * each method signature. Classes implementing this interface should implement
 * URI construction for collections and documents from the parameters given in each
 * method and the account data passed in to the constructor. Classes
 * implementing this interface are also responsible for security; access control
 * isn't currently supported in this interface, although we are considering
 * access control support in future versions of the interface.
 *
 * @category   Zend
 * @package    Zend_Cloud
 * @subpackage DocumentService
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
interface Zend_Cloud_DocumentService_Adapter
{
    // HTTP adapter to use for connections
    const HTTP_ADAPTER = 'http_adapter';

    /**
     * Create collection.
     *
     * @param  string $name
     * @param  array  $options
     * @return array
     */
    public function createCollection($name, $options = null);

    /**
     * Delete collection.
     *
     * @param  string $name
     * @param  array  $options
     * @return void
     */
    public function deleteCollection($name, $options = null);

       /**
     * List collections.
     *
     * @param  array  $options
     * @return array List of collection names
     */
    public function listCollections($options = null);

    /**
     * List all documents in a collection
     *
     * @param  string $collectionName
     * @param  null|array $options
     * @return Zend_Cloud_DocumentService_DocumentSet
     */
    public function listDocuments($collectionName, array $options = null);

    /**
     * Insert document
     *
     * @param  string $collectionName Collection name
     * @param  Zend_Cloud_DocumentService_Document $document Document to insert
     * @param  array $options
     * @return boolean
     */
    public function insertDocument($collectionName, $document, $options = null);

    /**
     * Replace document
     * The new document replaces the existing document with the same ID.
     *
     * @param string $collectionName Collection name
     * @param Zend_Cloud_DocumentService_Document $document
     * @param array $options
     */
    public function replaceDocument($collectionName, $document, $options = null);

    /**
     * Update document
     * The fields of the existing documents will be updated.
     * Fields not specified in the set will be left as-is.
     *
     * @param  string $collectionName
     * @param  mixed|Zend_Cloud_DocumentService_Document $documentID Document ID, adapter-dependent, or document containing updates
     * @param  array|Zend_Cloud_DocumentService_Document $fieldset Set of fields to update
     * @param  array                   $options
     * @return boolean
     */
    public function updateDocument($collectionName, $documentID, $fieldset = null, $options = null);

    /**
     * Delete document
     *
     * @param string $collectionName Collection name
     * @param mixed  $documentID Document ID, adapter-dependent
     * @param array  $options
     * @return void
     */
    public function deleteDocument($collectionName, $documentID, $options = null);

    /**
     * Fetch single document by ID
     *
     * Will return false if the document does not exist
     *
     * @param string $collectionName Collection name
     * @param mixed $documentID Document ID, adapter-dependent
     * @param array $options
     * @return Zend_Cloud_DocumentService_Document
     */
    public function fetchDocument($collectionName, $documentID, $options = null);

    /**
     * Query for documents stored in the document service. If a string is passed in
     * $query, the query string will be passed directly to the service.
     *
     * @param  string $collectionName Collection name
     * @param  string $query
     * @param  array $options
     * @return array Array of field sets
     */
    public function query($collectionName, $query, $options = null);

    /**
     * Create query statement
     *
     * @param string $fields
     * @return Zend_Cloud_DocumentService_Query
     */
    public function select($fields = null);

    /**
     * Get the concrete service client
     */
    public function getClient();
}
