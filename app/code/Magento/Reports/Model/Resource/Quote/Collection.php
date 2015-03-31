<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

/**
 * Reports quote collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Reports\Model\Resource\Quote;

class Collection extends \Magento\Quote\Model\Resource\Quote\Collection
{
    const SELECT_COUNT_SQL_TYPE_CART = 1;

    /**
     * @var int
     */
    protected $_selectCountSqlType = 0;

    /**
     * Join fields
     *
     * @var array
     */
    protected $_joinedFields = [];

    /**
     * Map
     *
     * @var array
     */
    protected $_map = ['fields' => ['store_id' => 'main_table.store_id']];

    /**
     * @var \Magento\Catalog\Model\Resource\Product\Collection
     */
    protected $_productResource;

    /**
     * @var \Magento\Customer\Model\Resource\Customer
     */
    protected $_customerResource;

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactory $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Catalog\Model\Resource\Product\Collection $productResource
     * @param \Magento\Customer\Model\Resource\Customer $customerResource
     * @param null $connection
     * @param \Magento\Framework\Model\Resource\Db\AbstractDb $resource
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Catalog\Model\Resource\Product\Collection $productResource,
        \Magento\Customer\Model\Resource\Customer $customerResource,
        $connection = null,
        \Magento\Framework\Model\Resource\Db\AbstractDb $resource = null
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->_productResource = $productResource;
        $this->_customerResource = $customerResource;
    }

    /**
     * Set type for COUNT SQL select
     *
     * @param int $type
     * @return $this
     */
    public function setSelectCountSqlType($type)
    {
        $this->_selectCountSqlType = $type;
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
     * Prepare select query for products in carts report
     *
     * @return $this
     */
    public function prepareForProductsInCarts()
    {
        $productAttrName = $this->_productResource->getAttribute('name');
        $productAttrNameId = (int)$productAttrName->getAttributeId();
        $productAttrNameTable = $productAttrName->getBackend()->getTable();
        $productAttrPrice = $this->_productResource->getAttribute('price');
        $productAttrPriceId = (int)$productAttrPrice->getAttributeId();
        $productAttrPriceTable = $productAttrPrice->getBackend()->getTable();

        $this->getSelect()->useStraightJoin(
            true
        )->reset(
            \Zend_Db_Select::COLUMNS
        )->joinInner(
            ['quote_items' => $this->getTable('quote_item')],
            'quote_items.quote_id = main_table.entity_id',
            null
        )->joinInner(
            ['e' => $this->getTable('catalog_product_entity')],
            'e.entity_id = quote_items.product_id',
            null
        )->joinInner(
            ['product_name' => $productAttrNameTable],
            'product_name.entity_id = e.entity_id'
                . ' AND product_name.attribute_id = ' . $productAttrNameId
                . ' AND product_name.store_id = ' . \Magento\Store\Model\Store::DEFAULT_STORE_ID,
            ['name' => 'product_name.value']
        )->joinInner(
            ['product_price' => $productAttrPriceTable],
            "product_price.entity_id = e.entity_id AND product_price.attribute_id = {$productAttrPriceId}",
            ['price' => new \Zend_Db_Expr('product_price.value * main_table.base_to_global_rate')]
        )->joinLeft(
            ['order_items' => new \Zend_Db_Expr(sprintf('(%s)', $this->getOrdersSubSelect()))],
            'order_items.product_id = e.entity_id',
            []
        )->columns(
            'e.*'
        )->columns(
            ['carts' => new \Zend_Db_Expr('COUNT(quote_items.item_id)')]
        )->columns(
            'order_items.orders'
        )->where(
            'main_table.is_active = ?',
            1
        )->group(
            'quote_items.product_id'
        );

        return $this;
    }

    /**
     * Orders quantity subselect
     *
     * @return \Magento\Framework\DB\Select
     */
    protected function getOrdersSubSelect()
    {
        $ordersSubSelect = clone $this->getSelect();
        $ordersSubSelect->reset()->from(
            ['oi' => $this->getTable('sales_order_item')],
            ['orders' => new \Zend_Db_Expr('COUNT(1)'), 'product_id']
        )->group(
            'oi.product_id'
        );

        return $ordersSubSelect;
    }

    /**
     * Add store ids to filter
     *
     * @param array $storeIds
     * @return $this
     */
    public function addStoreFilter($storeIds)
    {
        $this->addFieldToFilter('store_id', ['in' => $storeIds]);
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
        $customersSelect = $this->_customerResource->getReadConnection()->select();
        $customersSelect->from(['customer' => 'customer_entity'], 'entity_id');
        if (isset($filter['customer_name'])) {
            $customersSelect = $this->getCustomerNames($customersSelect);
            $customerName = $customersSelect->getAdapter()->getConcatSql(['cust_fname.value', 'cust_lname.value'], ' ');
            $customersSelect->where(
                $customerName . ' LIKE ?', '%' . $filter['customer_name'] . '%'
            );
        }
        if (isset($filter['email'])) {
            $customersSelect->where('customer.email LIKE ?', '%' . $filter['email'] . '%');
        }
        $filteredCustomers = $this->_customerResource->getReadConnection()->fetchCol($customersSelect);
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
     * Get select count sql
     *
     * @return string
     */
    public function getSelectCountSql()
    {
        $countSelect = clone $this->getSelect();
        $countSelect->reset(\Zend_Db_Select::ORDER);
        $countSelect->reset(\Zend_Db_Select::LIMIT_COUNT);
        $countSelect->reset(\Zend_Db_Select::LIMIT_OFFSET);
        $countSelect->reset(\Zend_Db_Select::COLUMNS);
        $countSelect->reset(\Zend_Db_Select::GROUP);
        $countSelect->resetJoinLeft();

        if ($this->_selectCountSqlType == self::SELECT_COUNT_SQL_TYPE_CART) {
            $countSelect->columns("COUNT(DISTINCT e.entity_id)");
        } else {
            $countSelect->columns("COUNT(DISTINCT main_table.entity_id)");
        }

        return $countSelect;
    }

    /**
     * @param \Magento\Framework\DB\Select $select
     * @return \Magento\Framework\DB\Select
     */
    protected function getCustomerNames($select)
    {
        $attrFirstname = $this->_customerResource->getAttribute('firstname');
        $attrFirstnameId = (int)$attrFirstname->getAttributeId();
        $attrFirstnameTableName = $attrFirstname->getBackend()->getTable();
        $attrLastname = $this->_customerResource->getAttribute('lastname');
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
            'cust_fname.attribute_id = ?', (int)$attrFirstnameId
        )->where(
            'cust_lname.attribute_id = ?', (int)$attrLastnameId
        );
        return $select;
    }

    /**
     * Resolve customers data based on ids quote table.
     *
     * @return void
     */
    public function resolveCustomerNames()
    {
        $select = $this->_customerResource->getReadConnection()->select();
        $customerName = $select->getAdapter()->getConcatSql(['cust_fname.value', 'cust_lname.value'], ' ');

        $select->from(
            ['customer' => 'customer_entity']
        )->columns(
            ['customer_name' => $customerName]
        )->where(
            'customer.entity_id IN (?)', array_column($this->getData(), 'customer_id')
        );
        $customersData = $select->getAdapter()->fetchAll($this->getCustomerNames($select));

        foreach($this->getItems() as $item) {
            $item->setData(array_merge($item->getData(), current($customersData)));
            next($customersData);
        }
    }
}
