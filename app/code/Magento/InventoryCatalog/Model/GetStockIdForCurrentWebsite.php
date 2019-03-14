<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Model;

use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\InventorySalesApi\Api\StockResolverInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Service for get stock id for current website.
 */
class GetStockIdForCurrentWebsite
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var StockResolverInterface
     */
    private $stockResolver;

    /**
     * @param StoreManagerInterface $storeManager
     * @param StockResolverInterface $stockResolver
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        StockResolverInterface $stockResolver
    ) {
        $this->storeManager = $storeManager;
        $this->stockResolver = $stockResolver;
    }

    /**
     * @return int
     */
    public function execute(): int
    {
        $websiteCode = $this->storeManager->getWebsite()->getCode();

        $stock = $this->stockResolver->execute(SalesChannelInterface::TYPE_WEBSITE, $websiteCode);
        $stockId = (int)$stock->getStockId();

        return $stockId;
    }
}
