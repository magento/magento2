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

/*
 * @see Zend_Cloud_DocumentService_QueryAdapter
 */
#require_once 'Zend/Cloud/DocumentService/QueryAdapter.php';

/**
 * Class implementing Query adapter for working with Azure queries in a
 * structured way
 *
 * @todo       Look into preventing a query injection attack.
 * @category   Zend
 * @package    Zend_Cloud
 * @subpackage DocumentService
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Cloud_DocumentService_Adapter_WindowsAzure_Query
    implements Zend_Cloud_DocumentService_QueryAdapter
{
    /**
     * Azure concrete query
     *
     * @var Zend_Service_WindowsAzure_Storage_TableEntityQuery
     */
    protected $_azureSelect;

    /**
     * Constructor
     *
     * @param  null|Zend_Service_WindowsAzure_Storage_TableEntityQuery $select Table select object
     * @return void
     */
    public function __construct($select = null)
    {
        if (!$select instanceof Zend_Service_WindowsAzure_Storage_TableEntityQuery) {
            #require_once 'Zend/Service/WindowsAzure/Storage/TableEntityQuery.php';
            $select = new Zend_Service_WindowsAzure_Storage_TableEntityQuery();
        }
        $this->_azureSelect = $select;
    }

    /**
     * SELECT clause (fields to be selected)
     *
     * Does nothing for Azure.
     *
     * @param  string $select
     * @return Zend_Cloud_DocumentService_Adapter_WindowsAzure_Query
     */
    public function select($select)
    {
        return $this;
    }

    /**
     * FROM clause (table name)
     *
     * @param string $from
     * @return Zend_Cloud_DocumentService_Adapter_WindowsAzure_Query
     */
    public function from($from)
    {
        $this->_azureSelect->from($from);
        return $this;
    }

    /**
     * WHERE clause (conditions to be used)
     *
     * @param string $where
     * @param mixed $value Value or array of values to be inserted instead of ?
     * @param string $op Operation to use to join where clauses (AND/OR)
     * @return Zend_Cloud_DocumentService_Adapter_WindowsAzure_Query
     */
    public function where($where, $value = null, $op = 'and')
    {
        if (!empty($value) && !is_array($value)) {
            // fix buglet in Azure - numeric values are quoted unless passed as an array
            $value = array($value);
        }
        $this->_azureSelect->where($where, $value, $op);
        return $this;
    }

    /**
     * WHERE clause for item ID
     *
     * This one should be used when fetching specific rows since some adapters
     * have special syntax for primary keys
     *
     * @param  array $value Row ID for the document (PartitionKey, RowKey)
     * @return Zend_Cloud_DocumentService_Adapter_WindowsAzure_Query
     */
    public function whereId($value)
    {
        if (!is_array($value)) {
            #require_once 'Zend/Cloud/DocumentService/Exception.php';
            throw new Zend_Cloud_DocumentService_Exception('Invalid document key');
        }
        $this->_azureSelect->wherePartitionKey($value[0])->whereRowKey($value[1]);
        return $this;
    }

    /**
     * LIMIT clause (how many rows to return)
     *
     * @param  int $limit
     * @return Zend_Cloud_DocumentService_Adapter_WindowsAzure_Query
     */
    public function limit($limit)
    {
        $this->_azureSelect->top($limit);
        return $this;
    }

    /**
     * ORDER BY clause (sorting)
     *
     * @todo   Azure service doesn't seem to support this yet; emulate?
     * @param  string $sort Column to sort by
     * @param  string $direction Direction - asc/desc
     * @return Zend_Cloud_DocumentService_Adapter_WindowsAzure_Query
     * @throws Zend_Cloud_OperationNotAvailableException
     */
    public function order($sort, $direction = 'asc')
    {
        #require_once 'Zend/Cloud/OperationNotAvailableException.php';
        throw new Zend_Cloud_OperationNotAvailableException('No support for sorting for Azure yet');
    }

    /**
     * Get Azure select query
     *
     * @return Zend_Service_WindowsAzure_Storage_TableEntityQuery
     */
    public function getAzureSelect()
    {
        return  $this->_azureSelect;
    }

    /**
     * Assemble query
     *
     * Simply return the WindowsAzure table entity query object
     *
     * @return Zend_Service_WindowsAzure_Storage_TableEntityQuery
     */
    public function assemble()
    {
        return $this->getAzureSelect();
    }
}
