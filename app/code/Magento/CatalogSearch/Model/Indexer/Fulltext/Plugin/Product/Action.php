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
     * @param array $attrData
     * @param int $storeId
     * @return ProductAction
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterUpdateAttributes(
        ProductAction $subject,
        ProductAction $action,
        $productIds,
        $attrData,
        $storeId
    ) {
        $this->reindexList(array_unique($productIds));

        return $action;
    }

    /**
     * Reindex on product websites mass change
     *
     * @param ProductAction $subject
     * @param null $result
     * @param array $productIds
     * @param array $websiteIds
     * @param string $type
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterUpdateWebsites(ProductAction $subject, $result, $productIds, $websiteIds, $type)
    {
        $this->reindexList(array_unique($productIds));
    }
}
