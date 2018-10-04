<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model;

/**
 * Product ID locator provides all product IDs by SKUs.
 */
class ProductIdLocator implements \Magento\Catalog\Model\ProductIdLocatorInterface
{
    /**
     * Limit values for array IDs by SKU.
     *
     * @var int
     */
    private $idsLimit;

    /**
     * Metadata pool.
     *
     * @var \Magento\Framework\EntityManager\MetadataPool
     */
    private $metadataPool;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    private $collectionFactory;

    /**
     * IDs by SKU cache.
     *
     * @var array
     */
    private $idsBySku = [];

    /**
     * Batch size to iterate collection
     *
     * @var int
     */
    private $batchSize;

    /**
     * @param \Magento\Framework\EntityManager\MetadataPool $metadataPool
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory
     * @param string $idsLimit
     * @param int $batchSize defines how many items can be processed by one request
     */
    public function __construct(
        \Magento\Framework\EntityManager\MetadataPool $metadataPool,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory,
        $idsLimit,
        int $batchSize = 5000
    ) {
        $this->metadataPool = $metadataPool;
        $this->collectionFactory = $collectionFactory;
        $this->idsLimit = (int)$idsLimit;
        $this->batchSize = $batchSize;
    }

    /**
     * {@inheritdoc}
     */
    public function retrieveProductIdsBySkus(array $skus)
    {
        $neededSkus = [];
        foreach ($skus as $sku) {
            $unifiedSku = strtolower(trim($sku));
            if (!isset($this->idsBySku[$unifiedSku])) {
                $neededSkus[] = $sku;
            }
        }

        if (!empty($neededSkus)) {
            /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
            $collection = $this->collectionFactory->create();
            $collection->addFieldToFilter(\Magento\Catalog\Api\Data\ProductInterface::SKU, ['in' => $neededSkus]);
            $linkField = $this->metadataPool->getMetadata(\Magento\Catalog\Api\Data\ProductInterface::class)
                ->getLinkField();

            $collection->setPageSize($this->batchSize);
            $pages = $collection->getLastPageNumber();
            for ($currentPage = 1; $currentPage <= $pages; $currentPage++) {
                $collection->setCurPage($currentPage);
                foreach ($collection->getItems() as $item) {
                    $sku = strtolower(trim($item->getSku()));
                    $itemIdentifier = $item->getData($linkField);
                    $this->idsBySku[$sku][$itemIdentifier] = $item->getTypeId();
                }
                $collection->clear();
            }
        }

        $productIds = [];
        foreach ($skus as $sku) {
            $unifiedSku = strtolower(trim($sku));
            if (isset($this->idsBySku[$unifiedSku])) {
                $productIds[$sku] = $this->idsBySku[$unifiedSku];
            }
        }
        $this->truncateToLimit();
        return $productIds;
    }

    /**
     * Cleanup IDs by SKU cache more than some limit.
     *
     * @return void
     */
    private function truncateToLimit()
    {
        if (count($this->idsBySku) > $this->idsLimit) {
            $this->idsBySku = array_slice($this->idsBySku, round($this->idsLimit / -2));
        }
    }
}
