<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Model;

use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\InventorySalesApi\Api\StockResolverInterface;

/**
 * Service for get stock id by website code.
 */
class GetStockIdForWebsiteByCode
{
    /**
     * @var StockResolverInterface
     */
    private $stockResolver;

    /**
     * @param StockResolverInterface $stockResolver
     */
    public function __construct(
        StockResolverInterface $stockResolver
    ) {
        $this->stockResolver = $stockResolver;
    }

    /**
     * @param string $websiteCode
     *
     * @return int
     */
    public function execute(string $websiteCode): int
    {
        $stock = $this->stockResolver->get(SalesChannelInterface::TYPE_WEBSITE, $websiteCode);
        $stockId = (int)$stock->getStockId();

        return $stockId;
    }
}
