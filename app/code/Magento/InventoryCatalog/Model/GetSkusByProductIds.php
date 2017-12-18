<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Model;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ResourceModel\Product as ProductResourceModel;

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
        $productsSku = array_column(
            $this->productResource->getProductsSku($productIds),
            ProductInterface::SKU,
            'entity_id'
        );
        return $productsSku;
    }
}
