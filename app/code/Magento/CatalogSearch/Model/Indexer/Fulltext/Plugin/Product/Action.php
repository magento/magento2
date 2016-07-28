<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Indexer\Fulltext\Plugin\Product;

use Magento\CatalogSearch\Model\Indexer\Fulltext\Plugin\AbstractPlugin as AbstractIndexerPlugin;
use Magento\Catalog\Model\Product\Action as ProductAction;

/**
 * Plugin for Magento\Catalog\Model\Product\Action
 */
class Action extends AbstractIndexerPlugin
{
    /**
     * Reindex on product attribute mass change
     *
     * @param ProductAction $subject
     * @param ProductAction $action
     * @param array $productIds
     * @return ProductAction
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterUpdateAttributes(ProductAction $subject, ProductAction $action, array $productIds)
    {
        $this->reindexList(array_unique($productIds));

        return $action;
    }

    /**
     * Reindex on product websites mass change
     *
     * @param ProductAction $subject
     * @param null $result
     * @param array $productIds
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterUpdateWebsites(ProductAction $subject, $result, array $productIds)
    {
        $this->reindexList(array_unique($productIds));
    }
}
