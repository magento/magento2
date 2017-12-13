<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ResourceModel\Product;
use Magento\Framework\App\ObjectManager;

/**
 * Product SKU locator provides all product SKUs by IDs.
 */
class ProductSkuLocator implements \Magento\Catalog\Model\ProductSkuLocatorInterface
{
    const CATALOG_PRODUCT_TABLE_NAME = 'catalog_product_entity';

    /**
     * Limit values for array SKUs by IDs.
     *
     * @var int
     */
    private $skusLimit;

    /**
     * IDs by SKU cache.
     *
     * @var array
     */
    private $skuByIds = [];

    /**
     * @var ResourceModel\Product
     */
    private $productResource;

    /**
     * @var LocatorService
     */
    private $locatorService;

    /**
     * SkuLocator constructor.
     *
     * @param Product             $productResource
     * @param                     $skusLimit
     * @param LocatorService|null $locatorService
     */
    public function __construct(
        Product $productResource,
        $skusLimit,
        LocatorService $locatorService = null
    ) {
        $this->productResource = $productResource;
        $this->skusLimit = (int)$skusLimit;
        $this->locatorService = $locatorService
            ?: ObjectManager::getInstance()->get(LocatorService::class);;
    }

    /**
     * {@inheritdoc}
     */
    public function retrieveSkusByProductIds(array $productIds): array
    {
        $resultProductIds = [];
        $neededIds = [];
        foreach ($productIds as $productId) {
            if (isset($this->skuByIds[$productId])) {
                $resultProductIds[$productId] = (string)$this->skuByIds[$productId];
            } else {
                $neededIds[] = $productId;
            }
        }

        if (!empty($neededIds)) {
            $items = array_column(
                $this->productResource->getProductsSku($neededIds),
                ProductInterface::SKU, 'entity_id'
            );

            $this->updateSkusCache($items);
            $resultProductIds += $items;
        }

        return $this->locatorService->truncateToLimit($resultProductIds, $this->skusLimit);
    }

    /**
     * @param array $additionalItems
     * @return ProductSkuLocator
     */
    private function updateSkusCache(array $additionalItems): ProductSkuLocator
    {
        $this->skuByIds += $additionalItems;

        return $this;
    }
}

