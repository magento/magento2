<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Model;

use Magento\Catalog\Model\ResourceModel\Product as ProductResourceModel;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\InventoryCatalog\Model\LocalCache\GetProductIdsBySkusCache;
use Magento\InventoryCatalogApi\Model\GetProductIdsBySkusInterface;

/**
 * @inheritdoc
 */
class GetProductIdsBySkus implements GetProductIdsBySkusInterface
{
    /**
     * @var ProductResourceModel
     */
    private $productResource;

    /**
     * @var GetProductIdsBySkusCache
     */
    private $getProductIdsBySkusCache;

    /**
     * @param ProductResourceModel $productResource
     * @param GetProductIdsBySkusCache $getProductIdsBySkusCache
     */
    public function __construct(
        ProductResourceModel $productResource,
        GetProductIdsBySkusCache $getProductIdsBySkusCache
    ) {
        $this->productResource = $productResource;
        $this->getProductIdsBySkusCache = $getProductIdsBySkusCache;
    }

    /**
     * @inheritdoc
     */
    public function execute(array $skus): array
    {
        $cacheKey = hash('md5', implode(',', $skus));

        if (null === $this->getProductIdsBySkusCache->get($cacheKey)) {
            $idsBySkus = $this->productResource->getProductsIdsBySkus($skus);
            $notFoundedSkus = array_diff($skus, array_keys($idsBySkus));

            if (!empty($notFoundedSkus)) {
                throw new NoSuchEntityException(
                    __('Following products with requested skus were not found: %1', implode($notFoundedSkus, ', '))
                );
            }

            $this->getProductIdsBySkusCache->set($cacheKey, $idsBySkus);
        }

        return $this->getProductIdsBySkusCache->get($cacheKey);
    }
}
