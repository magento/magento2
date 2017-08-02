<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogRule\Model\Indexer\Product;

use Magento\CatalogRule\Model\Indexer\AbstractIndexer;

/**
 * Class \Magento\CatalogRule\Model\Indexer\Product\ProductRuleIndexer
 *
 * @since 2.0.0
 */
class ProductRuleIndexer extends AbstractIndexer
{
    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    protected function doExecuteList($ids)
    {
        $this->indexBuilder->reindexByIds(array_unique($ids));
        $this->getCacheContext()->registerEntities(\Magento\Catalog\Model\Product::CACHE_TAG, $ids);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    protected function doExecuteRow($id)
    {
        $this->indexBuilder->reindexById($id);
    }
}
