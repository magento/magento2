<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogInventory\Model\Plugin;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\Framework\Exception\LocalizedException;

class AroundProductRepositorySave
{
    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $stockRegistry;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\CatalogInventory\Api\StockItemRepositoryInterface
     */
    private $stockItemRepository;

    /**
     * @var \Magento\CatalogInventory\Api\StockConfigurationInterface
     */
    private $stockConfiguration;

    /**
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\CatalogInventory\Api\StockItemRepositoryInterface $stockItemRepository
     * @param \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration
     */
    public function __construct(
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\CatalogInventory\Api\StockItemRepositoryInterface $stockItemRepository,
        \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration
    ) {
        $this->stockRegistry = $stockRegistry;
        $this->storeManager = $storeManager;
        $this->stockItemRepository = $stockItemRepository;
        $this->stockConfiguration = $stockConfiguration;
    }

    /**
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $subject
     * @param callable $proceed
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @param bool $saveOptions
     * @return \Magento\Catalog\Api\Data\ProductInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function aroundSave(
        \Magento\Catalog\Api\ProductRepositoryInterface $subject,
        \Closure $proceed,
        \Magento\Catalog\Api\Data\ProductInterface $product,
        $saveOptions = false
    ) {
        /** @var \Magento\Catalog\Api\Data\ProductInterface $result */
        $result = $proceed($product, $saveOptions);

        /* @var \Magento\CatalogInventory\Api\Data\StockItemInterface $stockItem */
        $stockItem = $this->getStockItemToBeUpdated($product);
        if ($stockItem === null) {
            return $result;
        }

        // set fields that the customer should not care about
        $stockItem->setProductId($result->getId());
        $stockItem->setWebsiteId($this->storeManager->getStore($result->getStoreId())->getWebsiteId());

        $this->stockRegistry->updateStockItemBySku($result->getSku(), $stockItem);

        // since we just saved a portion of the product, force a reload of it before returning it
        return $subject->get($result->getSku(), false, $result->getStoreId(), true);
    }

    /**
     * Return the stock item that needs to be updated.
     * If the stock item does not need to be updated, return null.
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @return StockItemInterface|null
     * @throws LocalizedException
     */
    protected function getStockItemToBeUpdated($product)
    {
        // from the API, all the data we care about will exist as extension attributes of the original product
        $stockItem = $this->getStockItemFromProduct($product);
        if ($stockItem !== null) {
            $defaultScopeId = $this->stockConfiguration->getDefaultScopeId();
            $defaultStockId = $this->stockRegistry->getStock($defaultScopeId)->getStockId();
            $stockId = $stockItem->getStockId();
            if ($stockId !== null && $stockId != $defaultStockId) {
                throw new LocalizedException(
                    __('Invalid stock id: %1. Only default stock with id %2 allowed', $stockId, $defaultStockId)
                );
            }
            $stockItemId = $stockItem->getItemId();
            if ($stockItemId !== null && (!is_numeric($stockItemId) || $stockItemId <= 0)) {
                throw new LocalizedException(
                    __('Invalid stock item id: %1. Should be null or numeric value greater than 0', $stockItemId)
                );
            }

            $defaultStockItemId = $this->stockRegistry->getStockItem($product->getId())->getItemId();
            if ($defaultStockItemId && $stockItemId !== null && $defaultStockItemId != $stockItemId) {
                throw new LocalizedException(
                    __('Invalid stock item id: %1. Assigned stock item id is %2', $stockItemId, $defaultStockItemId)
                );
            }
        } else {
            $stockItem = $this->stockRegistry->getStockItem($product->getId());
            if ($stockItem->getItemId() != null) {
                // we already have stock item info, so we return null since nothing more needs to be updated
                $stockItem = null;
            }
        }

        return $stockItem;
    }

    /**
     * @param ProductInterface $product
     * @return StockItemInterface
     */
    private function getStockItemFromProduct(ProductInterface $product)
    {
        $stockItem = null;
        $extendedAttributes = $product->getExtensionAttributes();
        if ($extendedAttributes !== null) {
            $stockItem = $extendedAttributes->getStockItem();
        }
        return $stockItem;
    }
}
