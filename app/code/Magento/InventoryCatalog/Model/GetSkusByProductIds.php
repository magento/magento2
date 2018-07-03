<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Model;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ResourceModel\Product as ProductResourceModel;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\InventoryCatalogApi\Model\GetSkusByProductIdsInterface;

/**
 * @inheritdoc
 */
class GetSkusByProductIds implements GetSkusByProductIdsInterface
{
    /**
     * @var ProductResourceModel
     */
    private $productResource;

    /**
     * @param ProductResourceModel $productResource
     */
    public function __construct(
        ProductResourceModel $productResource
    ) {
        $this->productResource = $productResource;
    }

    /**
     * @inheritdoc
     */
    public function execute(array $productIds): array
    {
        $skuByIds = array_column(
            $this->productResource->getProductsSku($productIds),
            ProductInterface::SKU,
            'entity_id'
        );
        $notFoundedIds = array_diff($productIds, array_keys($skuByIds));

        if (!empty($notFoundedIds)) {
            throw new NoSuchEntityException(
                __('Following products with requested ids were not found: %1', implode($notFoundedIds, ', '))
            );
        }

        $skuByIds = array_map('strval', $skuByIds);
        return $skuByIds;
    }
}
