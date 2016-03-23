<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Reports\Model\ResourceModel\Quote\Item;

use Magento\Framework\App\ResourceConnection;

/**
 * Collection of Magento\Quote\Model\Quote\Item
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Join fields
     *
     * @var array
     */
    protected $_joinedFields = [];

    /**
     * Fields map for correlation names & real selected fields
     *
     * @var array
     */
    protected $_map = ['fields' => ['store_id' => 'main_table.store_id']];

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected $productResource;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer
     */
    protected $customerResource;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Collection
     */
    protected $orderResource;

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactory $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $productResource
     * @param \Magento\Customer\Model\ResourceModel\Customer $customerResource
     * @param \Magento\Sales\Model\ResourceModel\Order\Collection $orderResource
     * @param \Magento\Framework\DB\Adapter\AdapterInterface $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Catalog\Model\ResourceModel\Product\Collection $productResource,
        \Magento\Customer\Model\ResourceModel\Customer $customerResource,
        \Magento\Sales\Model\ResourceModel\Order\Collection $orderResource,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $connection,
            $resource
        );
        $this->productResource = $productResource;
        $this->customerResource = $customerResource;
        $this->orderResource = $orderResource;
    }

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Quote\Model\Quote\Item', 'Magento\Quote\Model\ResourceModel\Quote\Item');
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
            ->from(['main_table' => $this->getTable('quote_item')], '')
            ->columns('main_table.product_id')
            ->columns(['carts' => new \Zend_Db_Expr('COUNT(main_table.item_id)')])
            ->columns('quote.base_to_global_rate')
            ->joinInner(
                ['quote' => $this->getTable('quote')],
                'main_table.quote_id = quote.entity_id',
                null
            )->where(
                'quote.is_active = ?',
                1
            )->group(
                'main_table.product_id'
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
     * Get select count sql
     *
     * @return \Magento\Framework\DB\Select
     */
    public function getSelectCountSql()
    {
        $countSelect = clone $this->prepareActiveCartItems();
        $countSelect->reset(\Magento\Framework\DB\Select::COLUMNS)
            ->reset(\Magento\Framework\DB\Select::GROUP)
            ->columns('COUNT(DISTINCT main_table.product_id)');
        return $countSelect;
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
        $productConnection = $this->productResource->getConnection();
        $productAttrName = $this->productResource->getAttribute('name');
        $productAttrNameId = (int)$productAttrName->getAttributeId();
        $productAttrPrice = $this->productResource->getAttribute('price');
        $productAttrPriceId = (int)$productAttrPrice->getAttributeId();

        $linkField = $this->productResource->getEntity()->getLinkField();
        $select = clone $this->productResource->getSelect();
        $select->reset();
        $select->from(
            ['main_table' => $this->getTable('catalog_product_entity')],
            ['main_table.entity_id', 'main_table.*']
        )->useStraightJoin(
            true
        )->joinInner(
            ['product_name' => $productAttrName->getBackend()->getTable()],
            "product_name.{$linkField} = main_table.{$linkField}"
            . ' AND product_name.attribute_id = ' . $productAttrNameId
            . ' AND product_name.store_id = ' . \Magento\Store\Model\Store::DEFAULT_STORE_ID,
            ['name' => 'product_name.value']
        )->joinInner(
            ['product_price' => $productAttrPrice->getBackend()->getTable()],
            "product_price.{$linkField} = main_table.{$linkField}"
            ." AND product_price.attribute_id = {$productAttrPriceId}",
            ['price' => new \Zend_Db_Expr('product_price.value')]
        )->where("main_table.entity_id IN (?)", $productIds);

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
