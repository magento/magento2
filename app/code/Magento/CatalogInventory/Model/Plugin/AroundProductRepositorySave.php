<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogInventory\Model\Plugin;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Exception\CouldNotSaveException;

/**
 * Plugin for Magento\Catalog\Api\ProductRepositoryInterface
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AroundProductRepositorySave
{
    /**
     * @var StockRegistryInterface
     */
    protected $stockRegistry;

    /**
     * @var StoreManagerInterface
     * @deprecated
     */
    protected $storeManager;

    /**
     * @var StockConfigurationInterface
     */
    private $stockConfiguration;

    /**
     * @param StockRegistryInterface $stockRegistry
     * @param StoreManagerInterface $storeManager
     * @param StockConfigurationInterface $stockConfiguration
     */
    public function __construct(
        StockRegistryInterface $stockRegistry,
        StoreManagerInterface $storeManager,
        StockConfigurationInterface $stockConfiguration
    ) {
        $this->stockRegistry = $stockRegistry;
        $this->storeManager = $storeManager;
        $this->stockConfiguration = $stockConfiguration;
    }

    /**
     * Save stock item information from received product
     *
     * Pay attention that in this code we mostly work with original product object to process stock item data,
     * not with received result (saved product) because it is already contains new empty stock item object.
     *
     * @param ProductRepositoryInterface $subject
     * @param ProductInterface $result
     * @param ProductInterface $product
     * @param bool $saveOptions
     * @return ProductInterface
     * @throws CouldNotSaveException
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSave(
        ProductRepositoryInterface $subject,
        ProductInterface $result,
        ProductInterface $product,
        $saveOptions = false
    ) {
        /* @var StockItemInterface $stockItem */
        $stockItem = $this->getStockItemToBeUpdated($product);
        if (null === $stockItem) {
            return $result;
        }

        // set fields that the customer should not care about
        $stockItem->setProductId($product->getId());
        $stockItem->setWebsiteId($this->stockConfiguration->getDefaultScopeId());

        $this->stockRegistry->updateStockItemBySku($product->getSku(), $stockItem);

        // since we just saved a portion of the product, force a reload of it before returning it
        return $subject->get($product->getSku(), false, $product->getStoreId(), true);
    }

    /**
     * Return the stock item that needs to be updated.
     * If the stock item does not need to be updated, return null.
     *
     * @param ProductInterface $product
     * @return StockItemInterface|null
     * @throws LocalizedException
     */
    protected function getStockItemToBeUpdated($product)
    {
        // from the API, all the data we care about will exist as extension attributes of the original product
        $stockItem = $this->getStockItemFromProduct($product);

        if ($stockItem === null) {
            $stockItem = $this->stockRegistry->getStockItem($product->getId());
            if ($stockItem->getItemId() !== null) {
                // we already have stock item info, so we return null since nothing more needs to be updated
                $stockItem = null;
            }
        }

        return $stockItem;
    }

    /**
     * @param ProductInterface $product
     * @return StockItemInterface
     * @throws LocalizedException
     */
    private function getStockItemFromProduct(ProductInterface $product)
    {
        $stockItem = null;
        $extendedAttributes = $product->getExtensionAttributes();
        if ($extendedAttributes !== null) {
            $stockItem = $extendedAttributes->getStockItem();
            if ($stockItem) {
                $this->validateStockItem($product, $stockItem);
            }
        }

        return $stockItem;
    }

    /**
     * @param ProductInterface $product
     * @param StockItemInterface $stockItem
     * @throws LocalizedException
     * @return void
     */
    private function validateStockItem(ProductInterface $product, StockItemInterface $stockItem)
    {
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
    }
}
