<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Reports quote collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Reports\Model\Resource\Quote;

class Collection extends \Magento\Sales\Model\Resource\Quote\Collection
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
     * @param \Magento\Core\Model\EntityFactory $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Catalog\Model\Resource\Product\Collection $productResource
     * @param \Magento\Customer\Model\Resource\Customer $customerResource
     * @param null $connection
     * @param \Magento\Framework\Model\Resource\Db\AbstractDb $resource
     */
    public function __construct(
        \Magento\Core\Model\EntityFactory $entityFactory,
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
        )->addSubtotal(
            $storeIds,
            $filter
        )->addCustomerData(
            $filter
        )->setOrder(
            'updated_at'
        );
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

        $ordersSubSelect = clone $this->getSelect();
        $ordersSubSelect->reset()->from(
            ['oi' => $this->getTable('sales_order_item')],
            ['orders' => new \Zend_Db_Expr('COUNT(1)'), 'product_id']
        )->group(
            'oi.product_id'
        );

        $this->getSelect()->useStraightJoin(
            true
        )->reset(
            \Zend_Db_Select::COLUMNS
        )->joinInner(
            ['quote_items' => $this->getTable('sales_quote_item')],
            'quote_items.quote_id = main_table.entity_id',
            null
        )->joinInner(
            ['e' => $this->getTable('catalog_product_entity')],
            'e.entity_id = quote_items.product_id',
            null
        )->joinInner(
            ['product_name' => $productAttrNameTable],
            "product_name.entity_id = e.entity_id\n                AND product_name.attribute_id = {$productAttrNameId}\n                AND product_name.store_id = " .
            \Magento\Store\Model\Store::DEFAULT_STORE_ID,
            ['name' => 'product_name.value']
        )->joinInner(
            ['product_price' => $productAttrPriceTable],
            "product_price.entity_id = e.entity_id AND product_price.attribute_id = {$productAttrPriceId}",
            ['price' => new \Zend_Db_Expr('product_price.value * main_table.base_to_global_rate')]
        )->joinLeft(
            ['order_items' => new \Zend_Db_Expr(sprintf('(%s)', $ordersSubSelect))],
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
     * @param unknown_type $filter
     * @return $this
     */
    public function addCustomerData($filter = null)
    {
        $attrFirstname = $this->_customerResource->getAttribute('firstname');
        $attrFirstnameId = (int)$attrFirstname->getAttributeId();
        $attrFirstnameTableName = $attrFirstname->getBackend()->getTable();

        $attrLastname = $this->_customerResource->getAttribute('lastname');
        $attrLastnameId = (int)$attrLastname->getAttributeId();
        $attrLastnameTableName = $attrLastname->getBackend()->getTable();

        $attrEmail = $this->_customerResource->getAttribute('email');
        $attrEmailTableName = $attrEmail->getBackend()->getTable();

        $adapter = $this->getSelect()->getAdapter();
        $customerName = $adapter->getConcatSql(['cust_fname.value', 'cust_lname.value'], ' ');
        $this->getSelect()->joinInner(
            ['cust_email' => $attrEmailTableName],
            'cust_email.entity_id = main_table.customer_id',
            ['email' => 'cust_email.email']
        )->joinInner(
            ['cust_fname' => $attrFirstnameTableName],
            implode(
                ' AND ',
                [
                    'cust_fname.entity_id = main_table.customer_id',
                    $adapter->quoteInto('cust_fname.attribute_id = ?', (int)$attrFirstnameId)
                ]
            ),
            ['firstname' => 'cust_fname.value']
        )->joinInner(
            ['cust_lname' => $attrLastnameTableName],
            implode(
                ' AND ',
                [
                    'cust_lname.entity_id = main_table.customer_id',
                    $adapter->quoteInto('cust_lname.attribute_id = ?', (int)$attrLastnameId)
                ]
            ),
            ['lastname' => 'cust_lname.value', 'customer_name' => $customerName]
        );

        $this->_joinedFields['customer_name'] = $customerName;
        $this->_joinedFields['email'] = 'cust_email.email';

        if ($filter) {
            if (isset($filter['customer_name'])) {
                $likeExpr = '%' . $filter['customer_name'] . '%';
                $this->getSelect()->where($this->_joinedFields['customer_name'] . ' LIKE ?', $likeExpr);
            }
            if (isset($filter['email'])) {
                $likeExpr = '%' . $filter['email'] . '%';
                $this->getSelect()->where($this->_joinedFields['email'] . ' LIKE ?', $likeExpr);
            }
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
}
