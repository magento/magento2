<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Report Customers Review collection
 */
namespace Magento\Reports\Model\ResourceModel\Review\Customer;

use Magento\Framework\DB\Select;

/**
 * Report Customer Review collection
 *
 * @api
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
class Collection extends \Magento\Review\Model\ResourceModel\Review\Collection
{
    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer
     */
    protected $_customerResource;

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactory $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Review\Helper\Data $reviewData
     * @param \Magento\Review\Model\Rating\Option\VoteFactory $voteFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Model\ResourceModel\Customer $customerResource
     * @param mixed $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Review\Helper\Data $reviewData,
        \Magento\Review\Model\Rating\Option\VoteFactory $voteFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\ResourceModel\Customer $customerResource,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        $this->_customerResource = $customerResource;
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $reviewData,
            $voteFactory,
            $storeManager,
            $connection,
            $resource
        );
    }

    /**
     * Init Select
     *
     * @return $this
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->_joinCustomers();
        return $this;
    }

    /**
     * Join customers
     *
     * @return $this
     */
    protected function _joinCustomers()
    {
        /** @var $connection \Magento\Framework\DB\Adapter\AdapterInterface */
        $connection = $this->getConnection();
        //Prepare fullname field result
        $customerFullname = $connection->getConcatSql(['customer.firstname', 'customer.lastname'], ' ');
        $this->getSelect()->reset(
            \Magento\Framework\DB\Select::COLUMNS
        )->joinInner(
            ['customer' => $this->getTable('customer_entity')],
            'customer.entity_id = detail.customer_id',
            []
        )->columns(
            [
                'customer_id' => 'detail.customer_id',
                'customer_name' => $customerFullname,
                'review_cnt' => 'COUNT(main_table.review_id)',
            ]
        )->group(
            'detail.customer_id'
        );

        return $this;
    }

    /**
     * @inheritdoc
     *
     * Additional processing of 'customer_name' field is required, as it is a concat field, which can not be aliased.
     * @see _joinCustomers
     * @since 100.2.2
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if ($field === 'review_cnt') {
            $conditionSql = $this->_getConditionSql($field, $condition);
            $this->getSelect()->having($conditionSql, null, Select::TYPE_CONDITION);
        }

        if ($field === 'customer_name') {
            $field = $this->getConnection()->getConcatSql(['customer.firstname', 'customer.lastname'], ' ');
        }

        return ($field === 'review_cnt') ? $this : parent::addFieldToFilter($field, $condition);
    }

    /**
     * Get select count sql
     *
     * @return \Magento\Framework\DB\Select
     * @throws \Zend_Db_Select_Exception
     */
    public function getSelectCountSql()
    {
        $countSelect = clone $this->getSelect();
        $havingClauses = $countSelect->getPart(Select::HAVING);
        $whereClauses = $countSelect->getPart(Select::WHERE);
        $countSelect->reset(\Magento\Framework\DB\Select::ORDER);
        $countSelect->reset(\Magento\Framework\DB\Select::LIMIT_COUNT);
        $countSelect->reset(\Magento\Framework\DB\Select::LIMIT_OFFSET);
        if (empty($whereClauses)) {
            $countSelect->reset(\Magento\Framework\DB\Select::WHERE);
        }
        if (empty($havingClauses)) {
            $countSelect->reset(\Magento\Framework\DB\Select::HAVING);
            $countSelect->columns(new \Zend_Db_Expr('COUNT(DISTINCT detail.customer_id)'));
        }
        $aggregateSelect = clone $countSelect;
        $aggregateSelect->reset();
        $aggregateSelect->from($countSelect, 'COUNT(*)');
        return $aggregateSelect;
    }
}
