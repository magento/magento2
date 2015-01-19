<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Log\Model\Resource;

/**
 * Customer log resource
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Customer extends \Magento\Framework\Model\Resource\Db\AbstractDb
{
    /**
     * Visitor data table name
     *
     * @var string
     */
    protected $_visitorTable;

    /**
     * Visitor info data table
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
     * Url info data table
     *
     * @var string
     */
    protected $_urlInfoTable;

    /**
     * Log URL data table name.
     *
     * @var string
     */
    protected $_urlTable;

    /**
     * Log quote data table name.
     *
     * @var string
     */
    protected $_quoteTable;

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('log_customer', 'log_id');

        $this->_visitorTable = $this->getTable('log_visitor');
        $this->_visitorInfoTable = $this->getTable('log_visitor_info');
        $this->_urlTable = $this->getTable('log_url');
        $this->_urlInfoTable = $this->getTable('log_url_info');
        $this->_customerTable = $this->getTable('log_customer');
        $this->_quoteTable = $this->getTable('log_quote');
    }

    /**
     * Retrieve select object for load object data
     *
     * @param string $field
     * @param mixed $value
     * @param \Magento\Log\Model\Customer $object
     * @return \Magento\Framework\DB\Select
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);
        if ($field == 'customer_id') {
            // load additional data by last login
            $table = $this->getMainTable();
            $select->joinInner(
                ['lvt' => $this->_visitorTable],
                "lvt.visitor_id = {$table}.visitor_id",
                ['last_visit_at']
            )->joinInner(
                ['lvit' => $this->_visitorInfoTable],
                'lvt.visitor_id = lvit.visitor_id',
                ['http_referer', 'remote_addr']
            )->joinInner(
                ['luit' => $this->_urlInfoTable],
                'luit.url_id = lvt.last_url_id',
                ['url']
            )->order(
                "{$table}.login_at DESC"
            )->limit(
                1
            );
        }
        return $select;
    }
}
