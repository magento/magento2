<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Rss\Product;

/**
 * Class NotifyStock
 * @package Magento\Catalog\Model\Rss\Product
 * @since 2.0.0
 */
class NotifyStock extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     * @since 2.0.0
     */
    protected $productFactory;

    /**
     * @var \Magento\CatalogInventory\Model\ResourceModel\StockFactory
     * @since 2.0.0
     */
    protected $stockFactory;

    /**
     * @var \Magento\Catalog\Model\Product\Attribute\Source\Status
     * @since 2.0.0
     */
    protected $productStatus;

    /**
     * Application Event Dispatcher
     *
     * @var \Magento\Framework\Event\ManagerInterface
     * @since 2.0.0
     */
    protected $eventManager;

    /**
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\CatalogInventory\Model\ResourceModel\StockFactory $stockFactory
     * @param \Magento\Catalog\Model\Product\Attribute\Source\Status $productStatus
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\CatalogInventory\Model\ResourceModel\StockFactory $stockFactory,
        \Magento\Catalog\Model\Product\Attribute\Source\Status $productStatus,
        \Magento\Framework\Event\ManagerInterface $eventManager
    ) {
        $this->productFactory = $productFactory;
        $this->stockFactory = $stockFactory;
        $this->productStatus = $productStatus;
        $this->eventManager = $eventManager;
    }

    /**
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     * @since 2.0.0
     */
    public function getProductsCollection()
    {
        /* @var $product \Magento\Catalog\Model\Product */
        $product = $this->productFactory->create();
        /* @var $collection \Magento\Catalog\Model\ResourceModel\Product\Collection */
        $collection = $product->getCollection();
        /** @var $resourceStock \Magento\CatalogInventory\Model\ResourceModel\Stock */
        $resourceStock = $this->stockFactory->create();
        $resourceStock->addLowStockFilter(
            $collection,
            ['qty', 'notify_stock_qty', 'low_stock_date', 'use_config' => 'use_config_notify_stock_qty']
        );
        $collection->addAttributeToSelect('name', true)
            ->addAttributeToFilter('status', ['in' => $this->productStatus->getVisibleStatusIds()])
            ->setOrder('low_stock_date');

        $this->eventManager->dispatch(
            'rss_catalog_notify_stock_collection_select',
            ['collection' => $collection]
        );
        return $collection;
    }
}
