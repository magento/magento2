<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySalesAdminUi\Model\ResourceModel;

use Magento\InventoryApi\Api\GetSourceItemsBySkuInterface;
use Magento\InventorySalesAdminUi\Model\GetStockSourceLinksBySourceCode;

/**
 * Get all stocks Ids by sku
 */
class GetAssignedStockIdsBySku
{
    /**
     * @var GetSourceItemsBySkuInterface
     */
    private $getSourceItemsBySku;

    /**
     * @var GetStockSourceLinksBySourceCode
     */
    private $getStockSourceLinksBySourceCode;

    /**
     * @param GetSourceItemsBySkuInterface $getSourceItemsBySku
     * @param GetStockSourceLinksBySourceCode $getStockSourceLinksBySourceCode
     */
    public function __construct(
        GetSourceItemsBySkuInterface $getSourceItemsBySku,
        GetStockSourceLinksBySourceCode $getStockSourceLinksBySourceCode
    ) {
        $this->getSourceItemsBySku = $getSourceItemsBySku;
        $this->getStockSourceLinksBySourceCode = $getStockSourceLinksBySourceCode;
    }

    /**
     * @param string $sku
     * @return array
     */
    public function execute(string $sku): array
    {
        $sourceItems = $this->getSourceItemsBySku->execute($sku);

        $stocksIds = [];
        foreach ($sourceItems as $sourceItem) {
            $stockSourceLinks = $this->getStockSourceLinksBySourceCode->execute($sourceItem->getSourceCode());
            foreach ($stockSourceLinks as $stockSourceLink) {
                $stocksIds[] = (int)$stockSourceLink->getStockId();
            }
        }

        return array_unique($stocksIds);
    }
}
