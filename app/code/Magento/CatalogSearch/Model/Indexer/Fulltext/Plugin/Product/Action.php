<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Model\Indexer\Fulltext\Plugin\Product;

use Magento\CatalogSearch\Model\Indexer\Fulltext\Plugin\AbstractPlugin;

class Action extends AbstractPlugin
{
    /**
     * Reindex on product attribute mass change
     *
     * @param \Magento\Catalog\Model\Product\Action $subject
     * @param \Magento\Catalog\Model\Product\Action $action
     * @param array $productIds
     * @return \Magento\Catalog\Model\Product\Action
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterUpdateAttributes(
        \Magento\Catalog\Model\Product\Action $subject,
        \Magento\Catalog\Model\Product\Action $action,
        array $productIds
    ) {
        $this->reindexList(array_unique($productIds));
        return $action;
    }

    /**
     * Reindex on product websites mass change
     *
     * @param \Magento\Catalog\Model\Product\Action $subject
     * @param null $result
     * @param array $productIds
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterUpdateWebsites(
        \Magento\Catalog\Model\Product\Action $subject,
        $result,
        array $productIds
    ) {
        $this->reindexList(array_unique($productIds));
    }
}
