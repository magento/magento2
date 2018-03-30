<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfigurableProductIndexer\Plugin\InventoryIndexer;

use Magento\Framework\Exception\StateException;
use Magento\InventoryConfigurableProductIndexer\Indexer\SourceItem\SourceItemIndexer
    as ConfigurableProductsSourceItemIndexer;
use Magento\InventoryIndexer\Indexer\SourceItem\SourceItemIndexer;

class SourceItemIndexerPlugin
{
    /**
     * @var ConfigurableProductsSourceItemIndexer
     */
    private $configurableProductsSourceItemIndexer;

    /**
     * @param ConfigurableProductsSourceItemIndexer $configurableProductsSourceItemIndexer
     */
    public function __construct(
        ConfigurableProductsSourceItemIndexer $configurableProductsSourceItemIndexer
    ) {
        $this->configurableProductsSourceItemIndexer = $configurableProductsSourceItemIndexer;
    }

    /**
     * @param SourceItemIndexer $subject
     * @param void $result
     * @param array $sourceItemIds
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws StateException
     */
    public function afterExecuteList(
        SourceItemIndexer $subject,
        $result,
        array $sourceItemIds
    ) {
        $this->configurableProductsSourceItemIndexer->executeList($sourceItemIds);
    }
}
