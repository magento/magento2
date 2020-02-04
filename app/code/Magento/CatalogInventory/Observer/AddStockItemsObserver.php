<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogInventory\Observer;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Api\StockItemCriteriaInterfaceFactory;
use Magento\CatalogInventory\Api\StockItemRepositoryInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Add Stock items to product collection.
 */
class AddStockItemsObserver implements ObserverInterface
{
    /**
     * @var StockItemCriteriaInterfaceFactory
     */
    private $criteriaInterfaceFactory;

    /**
     * @var StockItemRepositoryInterface
     */
    private $stockItemRepository;

    /**
     * @var StockConfigurationInterface
     */
    private $stockConfiguration;

    /**
     * AddStockItemsObserver constructor.
     *
     * @param StockItemCriteriaInterfaceFactory $criteriaInterfaceFactory
     * @param StockItemRepositoryInterface $stockItemRepository
     * @param StockConfigurationInterface $stockConfiguration
     */
    public function __construct(
        StockItemCriteriaInterfaceFactory $criteriaInterfaceFactory,
        StockItemRepositoryInterface $stockItemRepository,
        StockConfigurationInterface $stockConfiguration
    ) {
        $this->criteriaInterfaceFactory = $criteriaInterfaceFactory;
        $this->stockItemRepository = $stockItemRepository;
        $this->stockConfiguration = $stockConfiguration;
    }

    /**
     * Add stock items to products in collection.
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        /** @var Collection $productCollection */
        $productCollection = $observer->getData('collection');
        $productIds = array_keys($productCollection->getItems());
        $criteria = $this->criteriaInterfaceFactory->create();
        $criteria->setProductsFilter($productIds);
        $criteria->setScopeFilter($this->stockConfiguration->getDefaultScopeId());
        $stockItemCollection = $this->stockItemRepository->getList($criteria);
        foreach ($stockItemCollection->getItems() as $item) {
            /** @var Product $product */
            $product = $productCollection->getItemById($item->getProductId());
            $productExtension = $product->getExtensionAttributes();
            $productExtension->setStockItem($item);
            $product->setExtensionAttributes($productExtension);
        }
    }
}
