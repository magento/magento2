<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogInventory\Model\Plugin;

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
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->stockRegistry = $stockRegistry;
        $this->storeManager = $storeManager;
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
        if ($stockItem == null) {
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
     * @return \Magento\CatalogInventory\Api\Data\StockItemInterface|null
     */
    protected function getStockItemToBeUpdated($product)
    {
        // from the API, all the data we care about will exist as extension attributes of the original product
        $extendedAttributes = $product->getExtensionAttributes();
        if ($extendedAttributes !== null) {
            $stockItem = $extendedAttributes->getStockItem();
            if ($stockItem != null) {
                return $stockItem;
            }
        }

        // we have no new stock item information to update, however we need to ensure that the product does have some
        // stock item information present.  On a newly created product, it will not have any stock item info.
        $stockItem = $this->stockRegistry->getStockItem($product->getId());
        if ($stockItem->getItemId() != null) {
            // we already have stock item info, so we return null since nothing more needs to be updated
            return null;
        }

        return $stockItem;
    }
}
