<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfigurableProductIndexer\Indexer;

use Magento\InventoryConfigurableProductIndexer\Model\GetInStockConfigurationsCountPerStock;
use Magento\InventoryConfigurableProductIndexer\Model\GetParentConfigurableSkuList;
use Magento\InventoryConfigurableProductIndexer\Model\GetSkuListBySourceIds;

class SourceItemIndexer
{
    /**
     * @var GetInStockConfigurationsCountPerStock
     */
    private $getInStockConfigurationsCountPerStock;

    /**
     * @var SalableStatusChange
     */
    private $salableStatusChange;

    /**
     * @var GetSkuListBySourceIds
     */
    private $getSkuListBySourceIds;

    /**
     * @var GetParentConfigurableSkuList
     */
    private $getParentConfigurableSkuList;

    /**
     * @param GetSkuListBySourceIds $getSkuListBySourceIds
     * @param GetParentConfigurableSkuList $getParentConfigurableSkuList
     * @param GetInStockConfigurationsCountPerStock $getInStockConfigurationsCountPerStock
     * @param SalableStatusChange $salableStatusChange
     */
    public function __construct(
        GetSkuListBySourceIds $getSkuListBySourceIds,
        GetParentConfigurableSkuList $getParentConfigurableSkuList,
        GetInStockConfigurationsCountPerStock $getInStockConfigurationsCountPerStock,
        SalableStatusChange $salableStatusChange
    ) {
        $this->getSkuListBySourceIds = $getSkuListBySourceIds;
        $this->getParentConfigurableSkuList = $getParentConfigurableSkuList;
        $this->getInStockConfigurationsCountPerStock = $getInStockConfigurationsCountPerStock;
        $this->salableStatusChange = $salableStatusChange;
    }

    /**
     * @param array $sourceItemIds
     */
    public function executeList(array $sourceItemIds)
    {
        $childrenSkuList = $this->getSkuListBySourceIds->execute($sourceItemIds);
        $configurableSkuList = $this->getParentConfigurableSkuList->execute($childrenSkuList);

        foreach ($configurableSkuList as $configurableSku) {
            $statusPerStock = $this->getInStockConfigurationsCountPerStock->execute($configurableSku);
            $this->salableStatusChange->apply($configurableSku, $statusPerStock);
        }
    }
}
