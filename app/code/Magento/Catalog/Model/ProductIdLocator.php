<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model;

/**
 * Product ID locator provides all product IDs by SKUs.
 * @api
 */
class ProductIdLocator implements \Magento\Catalog\Model\ProductIdLocatorInterface
{
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
     * @param \Magento\Framework\EntityManager\MetadataPool $metadataPool
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory
     */
    public function __construct(
        \Magento\Framework\EntityManager\MetadataPool $metadataPool,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory
    ) {
        $this->metadataPool = $metadataPool;
        $this->collectionFactory = $collectionFactory;
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

            foreach ($collection as $item) {
                $this->idsBySku[strtolower(trim($item->getSku()))][$item->getData($linkField)] = $item->getTypeId();
            }
        }

        $productIds = [];
        foreach ($skus as $sku) {
            $unifiedSku = strtolower(trim($sku));
            if (isset($this->idsBySku[$unifiedSku])) {
                $productIds[$sku] = $this->idsBySku[$unifiedSku];
            }
        }

        return $productIds;
    }
}
