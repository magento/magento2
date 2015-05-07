<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Model\Resource\Quote;

class Collection extends \Magento\Quote\Model\Resource\Quote\Collection
{
    /**
     * @var \Magento\Customer\Model\Resource\Customer
     */
    protected $customerResource;

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Customer\Model\Resource\Customer $customerResource
     * @param \Zend_Db_Adapter_Abstract $connection
     * @param \Magento\Framework\Model\Resource\Db\AbstractDb $resource
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Customer\Model\Resource\Customer $customerResource,
        $connection = null,
        \Magento\Framework\Model\Resource\Db\AbstractDb $resource = null
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->customerResource = $customerResource;
    }

    /**
     * Prepare for abandoned report
     *
     * @param array $storeIds
     * @param string $filter
     * @return $this
     */
    public function prepareForAbandonedReport($storeIds, $filter = null)
    {
        $this->addFieldToFilter(
            'items_count',
            ['neq' => '0']
        )->addFieldToFilter(
            'main_table.is_active',
            '1'
        )->addFieldToFilter(
            'main_table.customer_id',
            ['neq' => null]
        )->addSubtotal(
            $storeIds,
            $filter
        )->setOrder(
            'updated_at'
        );
        if (isset($filter['email']) || isset($filter['customer_name'])) {
            $this->addCustomerData($filter);
        }
        if (is_array($storeIds) && !empty($storeIds)) {
            $this->addFieldToFilter('store_id', ['in' => $storeIds]);
        }

        return $this;
    }

    /**
     * Add subtotals
     *
     * @param array $storeIds
     * @param null|array $filter
     * @return $this
     */
    public function addSubtotal($storeIds = '', $filter = null)
    {
        if (is_array($storeIds)) {
            $this->getSelect()->columns(
                ['subtotal' => '(main_table.base_subtotal_with_discount*main_table.base_to_global_rate)']
            );
            $this->_joinedFields['subtotal'] = '(main_table.base_subtotal_with_discount*main_table.base_to_global_rate)';
        } else {
            $this->getSelect()->columns(['subtotal' => 'main_table.base_subtotal_with_discount']);
            $this->_joinedFields['subtotal'] = 'main_table.base_subtotal_with_discount';
        }

        if ($filter && is_array($filter) && isset($filter['subtotal'])) {
            if (isset($filter['subtotal']['from'])) {
                $this->getSelect()->where(
                    $this->_joinedFields['subtotal'] . ' >= ?',
                    $filter['subtotal']['from'],
                    \Zend_Db::FLOAT_TYPE
                );
            }
            if (isset($filter['subtotal']['to'])) {
                $this->getSelect()->where(
                    $this->_joinedFields['subtotal'] . ' <= ?',
                    $filter['subtotal']['to'],
                    \Zend_Db::FLOAT_TYPE
                );
            }
        }

        return $this;
    }

    /**
     * Resolve customers data based on ids quote table.
     *
     * @return void
     */
    public function resolveCustomerNames()
    {
        $select = $this->customerResource->getReadConnection()->select();
        $customerName = $select->getAdapter()->getConcatSql(['cust_fname.value', 'cust_lname.value'], ' ');

        $select->from(
            ['customer' => 'customer_entity']
        )->columns(
            ['customer_name' => $customerName]
        )->where(
            'customer.entity_id IN (?)',
            array_column(
                $this->getData(),
                'customer_id'
            )
        );
        $customersData = $select->getAdapter()->fetchAll($this->getCustomerNames($select));

        foreach ($this->getItems() as $item) {
            $item->setData(array_merge($item->getData(), current($customersData)));
            next($customersData);
        }
    }

    /**
     * @param \Magento\Framework\DB\Select $select
     * @return \Magento\Framework\DB\Select
     */
    protected function getCustomerNames($select)
    {
        $attrFirstname = $this->customerResource->getAttribute('firstname');
        $attrFirstnameId = (int)$attrFirstname->getAttributeId();
        $attrFirstnameTableName = $attrFirstname->getBackend()->getTable();
        $attrLastname = $this->customerResource->getAttribute('lastname');
        $attrLastnameId = (int)$attrLastname->getAttributeId();
        $attrLastnameTableName = $attrLastname->getBackend()->getTable();
        $select->joinInner(
            ['cust_fname' => $attrFirstnameTableName],
            'customer.entity_id = cust_fname.entity_id',
            ['firstname' => 'cust_fname.value']
        )->joinInner(
            ['cust_lname' => $attrLastnameTableName],
            'customer.entity_id = cust_lname.entity_id',
            ['lastname' => 'cust_lname.value']
        )->where(
            'cust_fname.attribute_id = ?',
            (int)$attrFirstnameId
        )->where(
            'cust_lname.attribute_id = ?',
            (int)$attrLastnameId
        );
        return $select;
    }
}
