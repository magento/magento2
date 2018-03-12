<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Model\Plugin;

use Magento\Catalog\Model\Product\Action as ProductAction;

/**
 * Plugin for Magento\Catalog\Model\Product\Action
 */
class ReindexUpdatedProducts
{
    /**
     * @var \Magento\CatalogInventory\Model\Indexer\Stock\Processor
     */
    private $indexerProcessor;

    /**
     * @param \Magento\CatalogInventory\Model\Indexer\Stock\Processor  $indexerProcessor
     */
    public function __construct(\Magento\CatalogInventory\Model\Indexer\Stock\Processor $indexerProcessor)
    {
        $this->indexerProcessor = $indexerProcessor;
    }

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
    public function afterUpdateAttributes(
        ProductAction $subject,
        ProductAction $action,
        $productIds
    ) {
        $this->indexerProcessor->reindexList(array_unique($productIds));
        return $action;
    }
}
