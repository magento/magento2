<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfigurableProductIndexer\Plugin\Indexer;

use Magento\InventoryIndexer\Indexer\SourceItem\SourceItemIndexer;

class UpdateSalableStatusForConfigurableProducts
{
    /**
     * @param SourceItemIndexer $subject
     * @param void $result
     * @param array $sourceItemIds
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecuteList(
        SourceItemIndexer $subject,
        $result,
        array $sourceItemIds
    ) {
        // $childrenSkuList = $this->skuListBySourceIdsProvider->execute($sourceItemIds);
        // $configurableProducts = $this->parentConfigurableProductsProvider->execute($childrenSkuList);
        //
    }
}
