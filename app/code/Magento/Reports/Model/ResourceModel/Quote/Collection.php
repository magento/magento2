<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Model\ResourceModel\Quote;

use Magento\Store\Model\Store;

/**
 * Collection of abandoned quotes with reports join.
 *
 * @api
 * @since 100.0.2
 */
class Collection extends \Magento\Quote\Model\ResourceModel\Quote\Collection
{
    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer
     */
    protected $customerResource;

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot $entitySnapshot
     * @param \Magento\Customer\Model\ResourceModel\Customer $customerResource
     * @param \Magento\Framework\DB\Adapter\AdapterInterface $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot $entitySnapshot,
        \Magento\Customer\Model\ResourceModel\Customer $customerResource,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $entitySnapshot,
            $connection,
            $resource
        );
        $this->customerResource = $customerResource;
    }

    /**
     * Filter collections by stores.
     *
     * @param array $storeIds
     * @param bool $withAdmin
     * @return $this
     * @since 100.3.1
     */
    public function addStoreFilter(array $storeIds, $withAdmin = true)
    {
        if ($withAdmin) {
            $storeIds[] = Store::DEFAULT_STORE_ID;
        }

        $this->addFieldToFilter('store_id', ['in' => $storeIds]);

        return $this;
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
     * Add customer data
     *
     * @param array|null $filter
     * @return $this
     */
    public function addCustomerData($filter = null)
    {
        $customersSelect = $this->customerResource->getConnection()->select();
        $customersSelect->from(
            ['customer' => $this->customerResource->getTable('customer_entity')],
            'entity_id'
        );
        if (isset($filter['customer_name'])) {
            $customerName = $this->customerResource->getConnection()
                ->getConcatSql(['customer.firstname', 'customer.lastname'], ' ');
            $customersSelect->where($customerName . ' LIKE ?', '%' . $filter['customer_name'] . '%');
        }
        if (isset($filter['email'])) {
            $customersSelect->where('customer.email LIKE ?', '%' . $filter['email'] . '%');
        }
        $filteredCustomers = $this->customerResource->getConnection()->fetchCol($customersSelect);
        $this->getSelect()->where('main_table.customer_id IN (?)', $filteredCustomers);
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
            $this->_joinedFields['subtotal'] =
                '(main_table.base_subtotal_with_discount*main_table.base_to_global_rate)';
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
        $select = $this->customerResource->getConnection()->select();
        $customerName = $this->customerResource->getConnection()->getConcatSql(['firstname', 'lastname'], ' ');

        $select->from(
            ['customer' => $this->customerResource->getTable('customer_entity')],
            ['entity_id', 'email']
        );
        $select->columns(
            ['customer_name' => $customerName]
        );
        $select->where(
            'customer.entity_id IN (?)',
            array_column(
                $this->getData(),
                'customer_id'
            )
        );
        $customersData = $this->customerResource->getConnection()->fetchAll($select);

        foreach ($this->getItems() as $item) {
            foreach ($customersData as $customerItemData) {
                if ($item['customer_id'] == $customerItemData['entity_id']) {
                    $item->setData(array_merge($item->getData(), $customerItemData));
                }
            }
        }
    }
}
