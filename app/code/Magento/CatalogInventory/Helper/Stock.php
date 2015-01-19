<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Helper;

use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class Stock
 */
class Stock
{
    /**
     * @var StockRegistryInterface
     */
    protected $stockRegistry;

    /**
     * Store model manager
     *
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Core store config
     *
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\CatalogInventory\Model\Resource\Stock\Status
     */
    protected $stockStatusResource;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManger;

    /**
     * @param StockRegistryInterface $stockRegistry
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        StockRegistryInterface $stockRegistry,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        ObjectManagerInterface $objectManager
    ) {
        $this->stockRegistry = $stockRegistry;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->objectManger = $objectManager;
    }

    /**
     * Assign stock status information to product
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param int $stockStatus
     * @return void
     */
    public function assignStatusToProduct(\Magento\Catalog\Model\Product $product, $stockStatus = null)
    {
        if (is_null($stockStatus)) {
            $websiteId = $product->getStore()->getWebsiteId();
            $stockStatus = $this->stockRegistry->getStockStatus($product->getId(), $websiteId);
            $status = $stockStatus->getStockStatus();
        }
        $product->setIsSalable($status);
    }

    /**
     * Add stock status information to products
     *
     * @param \Magento\Catalog\Model\Resource\Collection\AbstractCollection $productCollection
     * @return void
     */
    public function addStockStatusToProducts(
        \Magento\Catalog\Model\Resource\Collection\AbstractCollection $productCollection
    ) {
        $websiteId = $this->storeManager->getStore($productCollection->getStoreId())->getWebsiteId();
        $productIds = [];
        foreach ($productCollection as $product) {
            $productId = $product->getId();
            $stockStatus = $this->stockRegistry->getStockStatus($productId, $websiteId);
            $status = $stockStatus->getStockStatus();
            $product->setIsSalable($status);
        }
    }

    /**
     * Adds filtering for collection to return only in stock products
     *
     * @param \Magento\Catalog\Model\Resource\Product\Link\Product\Collection $collection
     * @return void
     */
    public function addInStockFilterToCollection($collection)
    {
        $manageStock = $this->scopeConfig->getValue(
            \Magento\CatalogInventory\Model\Configuration::XML_PATH_MANAGE_STOCK,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $cond = [
            '{{table}}.use_config_manage_stock = 0 AND {{table}}.manage_stock=1 AND {{table}}.is_in_stock=1',
            '{{table}}.use_config_manage_stock = 0 AND {{table}}.manage_stock=0'
        ];

        if ($manageStock) {
            $cond[] = '{{table}}.use_config_manage_stock = 1 AND {{table}}.is_in_stock=1';
        } else {
            $cond[] = '{{table}}.use_config_manage_stock = 1';
        }

        $collection->joinField(
            'inventory_in_stock',
            'cataloginventory_stock_item',
            'is_in_stock',
            'product_id=entity_id',
            '(' . join(') OR (', $cond) . ')'
        );
    }

    /**
     * Add stock status to prepare index select
     *
     * @param \Magento\Framework\DB\Select $select
     * @param \Magento\Store\Model\Website $website
     * @return void
     */
    public function addStockStatusToSelect(\Magento\Framework\DB\Select $select, \Magento\Store\Model\Website $website)
    {
        $resource = $this->getStockStatusResource();
        $resource->addStockStatusToSelect($select, $website);
    }

    /**
     * Add only is in stock products filter to product collection
     *
     * @param \Magento\Catalog\Model\Resource\Product\Collection $collection
     * @return void
     */
    public function addIsInStockFilterToCollection($collection)
    {
        $resource = $this->getStockStatusResource();
        $resource->addIsInStockFilterToCollection($collection);
    }

    /**
     * @return \Magento\CatalogInventory\Model\Resource\Stock\Status
     */
    protected function getStockStatusResource()
    {
        if (empty($this->stockStatusResource)) {
            $this->stockStatusResource = $this->objectManger->get(
                'Magento\CatalogInventory\Model\Resource\Stock\Status'
            );
        }
        return $this->stockStatusResource;
    }
}
