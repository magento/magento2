<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
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

        // all the data we care about will exist as extension attributes of the original product
        $extendedAttributes = $product->getExtensionAttributes();
        if ($extendedAttributes === null) {
            return $result;
        }

        /* @var \Magento\CatalogInventory\Api\Data\StockItemInterface $stockItem */
        $stockItem = $extendedAttributes->getStockItem();
        if ($stockItem == null) {
            return $result;
        }

        // set fields that the customer should not care about
        $stockItem->setProductId($result->getId());
        $stockItem->setWebsiteId($this->storeManager->getStore($result->getStoreId())->getWebsiteId());

        // TODO: might need to handle a *new* -v- *update* for the stockItem
        // ...   StockRegistry: $this->stockItemRepository->save
        // TODO: ensure this is correct logic for PUT/update and POST/create

        $this->stockRegistry->updateStockItemBySku($result->getSku(), $stockItem);

        // since we just saved a portion of the product, force a reload of it before returning it
        return $subject->get($result->getSku(), false, $result->getStoreId(), true);
    }
}
