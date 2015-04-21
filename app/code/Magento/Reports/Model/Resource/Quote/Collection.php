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

/**
 * Class Collection
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
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
    protected $productResource;

    /**
     * @var \Magento\Customer\Model\Resource\Customer
     */
    protected $customerResource;

    /**
     * @var \Magento\Sales\Model\Resource\Order\Collection
     */
    protected $orderResource;

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactory $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Catalog\Model\Resource\Product\Collection $productResource
     * @param \Magento\Customer\Model\Resource\Customer $customerResource
     * @param \Magento\Sales\Model\Resource\Order\Collection $orderResource
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
        \Magento\Sales\Model\Resource\Order\Collection $orderResource,
        $connection = null,
        \Magento\Framework\Model\Resource\Db\AbstractDb $resource = null
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->productResource = $productResource;
        $this->customerResource = $customerResource;
        $this->orderResource = $orderResource;
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
     * @return \Magento\Framework\DB\Select
     */
    public function prepareActiveCartItems()
    {
        $quoteItemsSelect = $this->getSelect();
        $quoteItemsSelect->reset()
            ->from(['main_table' => $this->getTable('quote')], '')
            ->columns('quote_items.product_id')
            ->columns(['carts' => new \Zend_Db_Expr('COUNT(quote_items.item_id)')])
            ->columns('main_table.base_to_global_rate')
            ->joinInner(
                ['quote_items' => $this->getTable('quote_item')],
                'quote_items.quote_id = main_table.entity_id',
                null
            )->where(
                'main_table.is_active = ?',
                1
            )->group(
                'quote_items.product_id'
            );

        return $quoteItemsSelect;
    }

    /**
     * Orders quantity data
     *
     * @param array $productIds
     * @return array
     */
    protected function getOrdersData(array $productIds)
    {
        $ordersSubSelect = clone $this->orderResource->getSelect();
        $ordersSubSelect->reset()->from(
            ['oi' => $this->getTable('sales_order_item')],
            ['product_id', 'orders' => new \Zend_Db_Expr('COUNT(1)')]
        )->where('oi.product_id IN (?)', $productIds)->group(
            'oi.product_id'
        );

        return $this->orderResource->getConnection()->fetchAssoc($ordersSubSelect);
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
        $customersSelect = $this->customerResource->getReadConnection()->select();
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
        $filteredCustomers = $this->customerResource->getReadConnection()->fetchCol($customersSelect);
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
     * @return \Magento\Framework\DB\Select
     */
    public function getSelectCountSql()
    {
        $countSelect = clone $this->prepareActiveCartItems();
        $countSelect->reset(\Zend_Db_Select::COLUMNS)
            ->reset(\Zend_Db_Select::GROUP)
            ->columns('COUNT(DISTINCT quote_items.product_id)');
        return $countSelect;
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
        $select = $this->customerResource->getReadConnection()->select();
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

    /**
     * Separate query for product and order data
     *
     * @param array $productIds
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getProductData(array $productIds)
    {
        $productConnection = $this->productResource->getConnection('read');
        $productAttrName = $this->productResource->getAttribute('name');
        $productAttrNameId = (int)$productAttrName->getAttributeId();
        $productAttrPrice = $this->productResource->getAttribute('price');
        $productAttrPriceId = (int)$productAttrPrice->getAttributeId();

        $select = clone $this->productResource->getSelect();
        $select->reset();
        $select->from(
            ['main_table' => $this->getTable('catalog_product_entity')]
        )->useStraightJoin(
            true
        )->joinInner(
            ['product_name' => $productAttrName->getBackend()->getTable()],
            'product_name.entity_id = main_table.entity_id'
            . ' AND product_name.attribute_id = ' . $productAttrNameId
            . ' AND product_name.store_id = ' . \Magento\Store\Model\Store::DEFAULT_STORE_ID,
            ['name' => 'product_name.value']
        )->joinInner(
            ['product_price' => $productAttrPrice->getBackend()->getTable()],
            "product_price.entity_id = main_table.entity_id AND product_price.attribute_id = {$productAttrPriceId}",
            ['price' => new \Zend_Db_Expr('product_price.value')]
        )->where('main_table.entity_id IN (?)', $productIds);

        $productData = $productConnection->fetchAssoc($select);
        return $productData;
    }

    /**
     * Add data fetched from another database
     *
     * @return $this
     */
    protected function _afterLoad()
    {
        parent::_afterLoad();
        $items = $this->getItems();
        $productIds = [];
        foreach ($items as $item) {
            $productIds[] = $item->getProductId();
        }
        $productData = $this->getProductData($productIds);
        $orderData = $this->getOrdersData($productIds);
        foreach ($items as $item) {
            $item->setId($item->getProductId());
            $item->setPrice($productData[$item->getProductId()]['price'] * $item->getBaseToGlobalRate());
            $item->setName($productData[$item->getProductId()]['name']);
            $item->setOrders(0);
            if (isset($orderData[$item->getProductId()])) {
                $item->setOrders($orderData[$item->getProductId()]['orders']);
            }
        }

        return $this;
    }
}
