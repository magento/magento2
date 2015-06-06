<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Report Customers Review collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Reports\Model\Resource\Review\Customer;

class Collection extends \Magento\Review\Model\Resource\Review\Collection
{
    /**
     * @var \Magento\Customer\Model\Resource\Customer
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
     * @param \Magento\Customer\Model\Resource\Customer $customerResource
     * @param mixed $connection
     * @param \Magento\Framework\Model\Resource\Db\AbstractDb $resource
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
        \Magento\Customer\Model\Resource\Customer $customerResource,
        $connection = null,
        \Magento\Framework\Model\Resource\Db\AbstractDb $resource = null
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
        /** @var $adapter \Magento\Framework\DB\Adapter\AdapterInterface */
        $adapter = $this->getConnection();
        //Prepare fullname field result
        $customerFullname = $adapter->getConcatSql(['customer.firstname', 'customer.lastname'], ' ');
        $this->getSelect()->reset(
            \Zend_Db_Select::COLUMNS
        )->joinInner(
            ['customer' => $adapter->getTableName('customer_entity')],
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
     * Get select count sql
     *
     * @return string
     */
    public function getSelectCountSql()
    {
        $countSelect = clone $this->_select;
        $countSelect->reset(\Zend_Db_Select::ORDER);
        $countSelect->reset(\Zend_Db_Select::GROUP);
        $countSelect->reset(\Zend_Db_Select::HAVING);
        $countSelect->reset(\Zend_Db_Select::LIMIT_COUNT);
        $countSelect->reset(\Zend_Db_Select::LIMIT_OFFSET);
        $countSelect->reset(\Zend_Db_Select::COLUMNS);

        $countSelect->columns(new \Zend_Db_Expr('COUNT(DISTINCT detail.customer_id)'));

        return $countSelect;
    }
}
