<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\ResourceModel\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\GetProductTypeBySkuInterface;
use Magento\Catalog\Model\ResourceModel\Product;

/**
 * @inheritdoc
 */
class GetProductTypeBySku implements GetProductTypeBySkuInterface
{
    /**
     * @var Product
     */
    private $productResource;

    /**
     * @param Product $productResource
     */
    public function __construct(
        Product $productResource
    ) {
        $this->productResource = $productResource;
    }

    /**
     * @inheritdoc
     */
    public function execute(array $skus)
    {
        if (empty($skus)) {
            return [];
        }

        $connection = $this->productResource->getConnection();
        $select = $connection->select()
            ->from(
                $this->productResource->getTable('catalog_product_entity'),
                [ProductInterface::SKU, ProductInterface::TYPE_ID]
            )->where(
                ProductInterface::SKU . ' IN (?)',
                $skus
            );

        return $connection->fetchPairs($select);
    }
}
