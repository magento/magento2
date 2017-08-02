<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Search\Model\ResourceModel;

use Magento\Framework\DB\Select;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Search\Model\Query as QueryModel;

/**
 * Search query resource model

 * @api
 * @since 2.0.0
 */
class Query extends AbstractDb
{
    /**
     * Date
     *
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     * @since 2.0.0
     */
    protected $_date;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     * @since 2.0.0
     */
    protected $dateTime;

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param string $connectionName
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        $connectionName = null
    ) {
        $this->_date = $date;
        $this->dateTime = $dateTime;
        parent::__construct($context, $connectionName);
    }

    /**
     * Custom load model only by query text (skip synonym for)
     *
     * @param AbstractModel $object
     * @param string $value
     * @return $this
     * @since 2.0.0
     */
    public function loadByQueryText(AbstractModel $object, $value)
    {
        $select = $this->getConnection()->select()->from(
            $this->getMainTable()
        )->where(
            'query_text = ?',
            $value
        )->where(
            'store_id = ?',
            $object->getStoreId()
        )->limit(
            1
        );
        $data = $this->getConnection()->fetchRow($select);
        if ($data) {
            $object->setData($data);
            $this->_afterLoad($object);
        }
        return $this;
    }

    /**
     * Loading string as a value or regular numeric
     *
     * @param AbstractModel $object
     * @param int|string $value
     * @param null|string $field
     * @return $this|\Magento\Framework\Model\ResourceModel\Db\AbstractDb
     * @SuppressWarnings("unused")
     * @since 2.0.0
     */
    public function load(AbstractModel $object, $value, $field = null)
    {
        if (is_numeric($value)) {
            return parent::load($object, $value);
        } else {
            $this->loadByQueryText($object, $value);
        }
        return $this;
    }

    /**
     * Custom load model by search query string
     *
     * @param AbstractModel $object
     * @param string $value
     * @return $this
     * @deprecated 2.1.0 "synonym for" feature has been removed
     * @since 2.0.0
     */
    public function loadByQuery(AbstractModel $object, $value)
    {
        $this->loadByQueryText($object, $value);
        return $this;
    }

    /**
     * @param AbstractModel $object
     * @return $this
     * @since 2.0.0
     */
    public function _beforeSave(AbstractModel $object)
    {
        $object->setUpdatedAt($this->dateTime->formatDate($this->_date->gmtTimestamp()));
        return $this;
    }

    /**
     * Init resource data
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_init('search_query', 'query_id');
    }

    /**
     * Save query with incremental popularity
     *
     * @param QueryModel $query
     * @return void
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 2.0.0
     */
    public function saveIncrementalPopularity(QueryModel $query)
    {
        $adapter = $this->getConnection();
        $table = $this->getMainTable();
        $saveData = [
            'store_id' => $query->getStoreId(),
            'query_text' => $query->getQueryText(),
            'popularity' => 1,
        ];
        $updateData = [
            'popularity' => new \Zend_Db_Expr('`popularity` + 1'),
        ];
        $adapter->insertOnDuplicate($table, $saveData, $updateData);
    }

    /**
     * Save query with number of results
     *
     * @param QueryModel $query
     * @return void
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 2.0.0
     */
    public function saveNumResults(QueryModel $query)
    {
        $adapter = $this->getConnection();
        $table = $this->getMainTable();
        $numResults = $query->getNumResults();
        $saveData = [
            'store_id' => $query->getStoreId(),
            'query_text' => $query->getQueryText(),
            'num_results' => $numResults,
        ];
        $updateData = ['num_results' => $numResults];
        $adapter->insertOnDuplicate($table, $saveData, $updateData);
    }
}
