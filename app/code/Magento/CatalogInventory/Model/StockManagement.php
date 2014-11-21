<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\CatalogInventory\Model;

use Magento\CatalogInventory\Api\StockManagementInterface;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Model\Spi\StockRegistryProviderInterface;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\Catalog\Model\ProductFactory;

/**
 * Class StockManagement
 * @package Magento\CatalogInventory\Model
 * @api
 * @spi
 */
class StockManagement implements StockManagementInterface
{
    /**
     * @var StockRegistryProviderInterface
     */
    protected $stockRegistryProvider;

    /**
     * @var StockState
     */
    protected $stockState;

    /**
     * @var StockConfigurationInterface
     */
    protected $stockConfiguration;

    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Magento\CatalogInventory\Model\Resource\Stock
     */
    protected $resource;

    /**
     * @param StockRegistryProviderInterface $stockRegistryProvider
     * @param StockState $stockState
     * @param StockConfigurationInterface $stockConfiguration
     * @param ProductFactory $productFactory
     */
    public function __construct(
        StockRegistryProviderInterface $stockRegistryProvider,
        StockState $stockState,
        StockConfigurationInterface $stockConfiguration,
        ProductFactory $productFactory
    ) {
        $this->stockRegistryProvider = $stockRegistryProvider;
        $this->stockState = $stockState;
        $this->stockConfiguration = $stockConfiguration;
        $this->productFactory = $productFactory;
    }

    /**
     * Subtract product qtys from stock.
     * Return array of items that require full save
     *
     * @param array $items
     * @param int $websiteId
     * @return StockItemInterface[]
     * @throws \Magento\Framework\Model\Exception
     */
    public function registerProductsSale($items, $websiteId = null)
    {
        //if (!$websiteId) {
            $websiteId = $this->stockConfiguration->getDefaultWebsiteId();
        //}
        $this->getResource()->beginTransaction();
        $lockedItems = $this->getResource()->lockProductsStock(array_keys($items), $websiteId);
        $fullSaveItems = $registeredItems = [];
        foreach ($lockedItems as $lockedItemRecord) {
            $productId = $lockedItemRecord['product_id'];
            /** @var StockItemInterface $stockItem */
            $orderedQty = $items[$productId];
            $stockItem = $this->stockRegistryProvider->getStockItem($productId, $websiteId);
            $canSubtractQty = $stockItem->getId() && $this->canSubtractQty($stockItem);
            if (!$canSubtractQty || !$this->stockConfiguration->isQty($this->getProductType($productId))) {
                continue;
            }
            if (!$stockItem->hasAdminArea()
                && !$this->stockState->checkQty($productId, $orderedQty, $stockItem->getWebsiteId())
            ) {
                $this->getResource()->commit();
                throw new \Magento\Framework\Model\Exception(
                    __('Not all of your products are available in the requested quantity.')
                );
            }
            if ($this->canSubtractQty($stockItem)) {
                $stockItem->setQty($stockItem->getQty() - $orderedQty);
            }
            $registeredItems[$productId] = $orderedQty;
            if (!$this->stockState->verifyStock($productId, $stockItem->getWebsiteId())
                || $this->stockState->verifyNotification(
                    $productId,
                    $stockItem->getWebsiteId()
                )
            ) {
                $fullSaveItems[] = $stockItem;
            }
        }
        $this->getResource()->correctItemsQty($registeredItems, $websiteId, '-');
        $this->getResource()->commit();
        return $fullSaveItems;
    }

    /**
     * @param array $items
     * @param int $websiteId
     * @return void
     */
    public function revertProductsSale(array $items, $websiteId = null)
    {
        //if (!$websiteId) {
        $websiteId = $this->stockConfiguration->getDefaultWebsiteId();
        //}
        $this->getResource()->correctItemsQty($items, $websiteId, '+');
    }

    /**
     * Get back to stock (when order is canceled or whatever else)
     *
     * @param int $productId
     * @param int|float $qty
     * @param int $websiteId
     * @return void
     */
    public function backItemQty($productId, $qty, $websiteId = null)
    {
        //if (!$websiteId) {
        $websiteId = $this->stockConfiguration->getDefaultWebsiteId();
        //}
        $stockItem = $this->stockRegistryProvider->getStockItem($productId, $websiteId);
        if ($stockItem->getId() && $this->stockConfiguration->isQty($this->getProductType($productId))) {
            if ($this->canSubtractQty($stockItem)) {
                $stockItem->setQty($stockItem->getQty() + $qty);
            }
            if ($this->stockConfiguration->getCanBackInStock($stockItem->getStoreId()) && $stockItem->getQty()
                > $stockItem->getMinQty()
            ) {
                $stockItem->setIsInStock(true);
                $stockItem->setStockStatusChangedAutomaticallyFlag(true);
            }
            $stockItem->save();
        }
    }

    /**
     * Get Product type
     *
     * @param int $productId
     * @return string
     * @deprecated
     */
    protected function getProductType($productId)
    {
        $product = $this->productFactory->create();
        $product->load($productId);
        return $product->getTypeId();
    }

    /**
     * @return \Magento\CatalogInventory\Model\Resource\Stock
     */
    protected function getResource()
    {
        if (empty($this->resource)) {
            $this->resource = \Magento\Framework\App\ObjectManager::getInstance()->get(
                'Magento\CatalogInventory\Model\Resource\Stock'
            );
        }
        return $this->resource;
    }

    /**
     * Check if is possible subtract value from item qty
     *
     * @param StockItemInterface $stockItem
     * @return bool
     */
    protected function canSubtractQty(StockItemInterface $stockItem)
    {
        return $stockItem->getManageStock() && $this->stockConfiguration->canSubtractQty();
    }
}
