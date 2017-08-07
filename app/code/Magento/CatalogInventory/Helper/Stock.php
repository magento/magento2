<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Helper;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Collection\AbstractCollection;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Model\ResourceModel\Stock\Status;
use Magento\CatalogInventory\Model\ResourceModel\Stock\StatusFactory;
use Magento\CatalogInventory\Model\Spi\StockRegistryProviderInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Stock
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Stock
{
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
     * @var Status
     */
    protected $stockStatusResource;

    /**
     * @var StatusFactory
     */
    protected $stockStatusFactory;

    /**
     * @var StockRegistryProviderInterface
     */
    private $stockRegistryProvider;

    /**
     * @var StockConfigurationInterface
     * @since 2.1.0
     */
    private $stockConfiguration;

    /**
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param StatusFactory $stockStatusFactory
     * @param StockRegistryProviderInterface $stockRegistryProvider
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        StatusFactory $stockStatusFactory,
        StockRegistryProviderInterface $stockRegistryProvider
    ) {
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->stockStatusFactory  = $stockStatusFactory;
        $this->stockRegistryProvider = $stockRegistryProvider;
    }

    /**
     * Assign stock status information to product
     *
     * @param Product $product
     * @param int $status
     * @return void
     */
    public function assignStatusToProduct(Product $product, $status = null)
    {
        if ($status === null) {
            $scopeId = $this->getStockConfiguration()->getDefaultScopeId();
            $stockStatus = $this->stockRegistryProvider->getStockStatus($product->getId(), $scopeId);
            $status = $stockStatus->getStockStatus();
        }
        $product->setIsSalable($status);
    }

    /**
     * Add stock status information to products
     *
     * @param AbstractCollection $productCollection
     * @deprecated 2.1.0 Use Stock::addIsInStockFilterToCollection instead
     * @return void
     */
    public function addStockStatusToProducts(AbstractCollection $productCollection)
    {
        $scopeId = $this->getStockConfiguration()->getDefaultScopeId();
        foreach ($productCollection as $product) {
            $productId = $product->getId();
            $stockStatus = $this->stockRegistryProvider->getStockStatus($productId, $scopeId);
            $status = $stockStatus->getStockStatus();
            $product->setIsSalable($status);
        }
    }

    /**
     * Adds filtering for collection to return only in stock products
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product\Link\Product\Collection $collection
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
     * Add only is in stock products filter to product collection
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @return void
     */
    public function addIsInStockFilterToCollection($collection)
    {
        $stockFlag = 'has_stock_status_filter';
        if (!$collection->hasFlag($stockFlag)) {
            $isShowOutOfStock = $this->scopeConfig->getValue(
                \Magento\CatalogInventory\Model\Configuration::XML_PATH_SHOW_OUT_OF_STOCK,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
            $resource = $this->getStockStatusResource();
            $resource->addStockDataToCollection(
                $collection,
                !$isShowOutOfStock || $collection->getFlag('require_stock_items')
            );
            $collection->setFlag($stockFlag, true);
        }
    }

    /**
     * @return Status
     */
    protected function getStockStatusResource()
    {
        if (empty($this->stockStatusResource)) {
            $this->stockStatusResource = $this->stockStatusFactory->create();
        }
        return $this->stockStatusResource;
    }

    /**
     * @return StockConfigurationInterface
     *
     * @deprecated 2.1.0
     * @since 2.1.0
     */
    private function getStockConfiguration()
    {
        if ($this->stockConfiguration === null) {
            $this->stockConfiguration = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\CatalogInventory\Api\StockConfigurationInterface::class);
        }
        return $this->stockConfiguration;
    }
}
