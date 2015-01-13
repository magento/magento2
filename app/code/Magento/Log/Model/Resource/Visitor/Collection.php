<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Log\Model\Resource\Visitor;

/**
 * Visitor log collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Collection extends \Magento\Framework\Model\Resource\Db\Collection\AbstractCollection
{
    /**
     * Visitor data table name
     *
     * @var string
     */
    protected $_visitorTable;

    /**
     * Visitor data info table name
     *
     * @var string
     */
    protected $_visitorInfoTable;

    /**
     * Customer data table
     *
     * @var string
     */
    protected $_customerTable;

    /**
     * Log URL data table name.
     *
     * @var string
     */
    protected $_urlTable;

    /**
     * Log URL expanded data table name.
     *
     * @var string
     */
    protected $_urlInfoTable;

    /**
     * Aggregator data table.
     *
     * @var string
     */
    protected $_summaryTable;

    /**
     * Aggregator type data table.
     *
     * @var string
     */
    protected $_summaryTypeTable;

    /**
     * Quote data table.
     *
     * @var string
     */
    protected $_quoteTable;

    /**
     * Online filter used flag
     *
     * @var bool
     */
    protected $_isOnlineFilterUsed = false;

    /**
     * Field map
     *
     * @var array
     */
    protected $_fieldMap = [
        'customer_firstname' => 'customer_firstname_table.value',
        'customer_lastname' => 'customer_lastname_table.value',
        'customer_email' => 'customer_email_table.email',
        'customer_id' => 'customer_table.customer_id',
        'url' => 'url_info_table.url',
    ];

    /**
     * Collection resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Log\Model\Visitor', 'Magento\Log\Model\Resource\Visitor');

        $this->_visitorTable = $this->getTable('log_visitor');
        $this->_visitorInfoTable = $this->getTable('log_visitor_info');
        $this->_urlTable = $this->getTable('log_url');
        $this->_urlInfoTable = $this->getTable('log_url_info');
        $this->_customerTable = $this->getTable('log_customer');
        $this->_summaryTable = $this->getTable('log_summary');
        $this->_summaryTypeTable = $this->getTable('log_summary_type');
        $this->_quoteTable = $this->getTable('log_quote');
    }

    /**
     * Filter for customers only
     *
     * @return $this
     */
    public function showCustomersOnly()
    {
        $this->getSelect()->where('customer_table.customer_id > 0')->group('customer_table.customer_id');
        return $this;
    }

    /**
     * Filter by customer ID, as 'type' field does not exist
     *
     * @param string $fieldName
     * @param array $condition
     * @return $this
     */
    public function addFieldToFilter($fieldName, $condition = null)
    {
        if ($fieldName == 'type' && is_array($condition) && isset($condition['eq'])) {
            $fieldName = 'customer_id';
            if ($condition['eq'] === \Magento\Customer\Model\Visitor::VISITOR_TYPE_VISITOR) {
                $condition = ['null' => 1];
            } else {
                $condition = ['moreq' => 1];
            }
        }
        return parent::addFieldToFilter($this->_getFieldMap($fieldName), $condition);
    }

    /**
     * Return field with table prefix
     *
     * @param string $fieldName
     * @return string
     */
    protected function _getFieldMap($fieldName)
    {
        if (isset($this->_fieldMap[$fieldName])) {
            return $this->_fieldMap[$fieldName];
        } else {
            return 'main_table.' . $fieldName;
        }
    }

    /**
     * Load data
     *
     * @param boolean $printQuery
     * @param boolean $logQuery
     * @return \Magento\Framework\Model\Resource\Db\Collection\AbstractCollection
     */
    public function load($printQuery = false, $logQuery = false)
    {
        if ($this->isLoaded()) {
            return $this;
        }
        $this->_eventManager->dispatch('log_visitor_collection_load_before', ['collection' => $this]);
        return parent::load($printQuery, $logQuery);
    }

    /**
     * Return true if online filter used
     *
     * @return bool
     */
    public function getIsOnlineFilterUsed()
    {
        return $this->_isOnlineFilterUsed;
    }

    /**
     * Filter visitors by specified store ids
     *
     * @param int[]|int $storeIds
     * @return void
     */
    public function addVisitorStoreFilter($storeIds)
    {
        $this->getSelect()->where('visitor_table.store_id IN (?)', $storeIds);
    }
}
