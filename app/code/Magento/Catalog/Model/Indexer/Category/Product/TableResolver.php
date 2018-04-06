<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Indexer\Category\Product;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Search\Request\Dimension;
use Magento\Framework\Search\Request\IndexScopeResolverInterface;

class TableResolver implements IndexScopeResolverInterface
{
    /**
     * Catalog category index table name
     */
    const MAIN_INDEX_TABLE = 'catalog_category_product_index';

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
     * @param string $index
     * @param Dimension[] $dimensions
     * @return string
     */
    public function resolve($index, array $dimensions)
    {
        $tableNameParts = [$index];
        foreach ($dimensions as $dimension) {
            $tableNameParts[] = $dimension->getName() . '_' . $dimension->getValue();
        }
        return $this->resource->getTableName(implode('_', $tableNameParts));
    }
}
