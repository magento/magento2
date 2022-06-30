<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Reports\Model\Product;

use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Retrieve products data for reports by entity id's
 */
class DataRetriever
{
    /**
     * @var ProductCollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * DataRetriever constructor.
     *
     * @param ProductCollectionFactory $productCollectionFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ProductCollectionFactory $productCollectionFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * Retrieve products data by entity id's
     *
     * @param array $entityIds
     * @return array
     */
    public function execute(array $entityIds = []): array
    {
        $productCollection = $this->getProductCollection($entityIds);

        return $this->prepareDataByCollection($productCollection);
    }

    /**
     * Get product collection filtered by entity id's
     *
     * @param array $entityIds
     * @return ProductCollection
     */
    private function getProductCollection(array $entityIds = []): ProductCollection
    {
        $productCollection = $this->productCollectionFactory->create();
        $productCollection->addAttributeToSelect('name');
        $productCollection->addIdFilter($entityIds);
        $productCollection->addPriceData(null, $this->getWebsiteIdForFilter());

        return $productCollection;
    }

    /**
     * Retrieve website id for filter collection
     *
     * @return int
     */
    private function getWebsiteIdForFilter(): int
    {
        $defaultStoreView = $this->storeManager->getDefaultStoreView();
        if ($defaultStoreView) {
            $websiteId = (int)$defaultStoreView->getWebsiteId();
        } else {
            $websites = $this->storeManager->getWebsites();
            $website = reset($websites);
            $websiteId = (int)$website->getId();
        }

        return $websiteId;
    }

    /**
     * Prepare data by collection
     *
     * @param ProductCollection $productCollection
     * @return array
     */
    private function prepareDataByCollection(ProductCollection $productCollection): array
    {
        $productsData = [];
        foreach ($productCollection as $product) {
            $productsData[$product->getId()] = $product->getData();
        }

        return $productsData;
    }
}
