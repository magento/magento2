<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CatalogImportExport\Model\ResourceModel;

use Magento\Catalog\Model\ResourceModel\Product;

class ProductDataLoader
{
    /**
     * @var Product
     */
    private Product $productResource;

    /**
     * @param Product $productResource
     */
    public function __construct(Product $productResource)
    {
        $this->productResource = $productResource;
    }

    /**
     * Get all products' columns from db
     *
     * @param array $columns
     * @return \Generator
     * @throws \Zend_Db_Statement_Exception
     */
    public function getProductsData(array $columns): \Generator
    {
        $resource = $this->productResource;
        $connection = $resource->getConnection();
        $select = $connection->select()->from($resource->getTable('catalog_product_entity'), $columns);

        $stmt = $connection->query($select);

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            yield $row;
        }
    }
}
