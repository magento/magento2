<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Model\ResourceModel;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\App\ResourceConnection;

/**
 * Provides all product SKUs by ProductIds. Key is sku, value is product id
 */
class GetProductTypesBySkus
{
    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @param ResourceConnection $resource
     */
    public function __construct(
        ResourceConnection $resource
    ) {
        $this->resource = $resource;
    }

    /**
     * @param array $skus
     * @return array
     */
    public function execute(array $skus): array
    {
        $connection = $this->resource->getConnection();
        $productTable = $this->resource->getTableName('catalog_product_entity');

        $select = $connection->select()
            ->from(
                $productTable,
                [ProductInterface::SKU, ProductInterface::TYPE_ID]
            )->where(
                ProductInterface::SKU . ' IN (?)',
                $skus
            );

        return $connection->fetchPairs($select);
    }
}
