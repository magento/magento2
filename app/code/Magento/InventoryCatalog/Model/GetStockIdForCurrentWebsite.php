<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Model;

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
     * @var GetStockIdForWebsiteByCode
     */
    private $getStockIdForWebsiteByCode;

    /**
     * @param StoreManagerInterface $storeManager
     * @param StockResolverInterface $stockResolver
     * @param GetStockIdForWebsiteByCode $getStockIdForWebsiteByCode
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        StockResolverInterface $stockResolver,
        GetStockIdForWebsiteByCode $getStockIdForWebsiteByCode
    ) {
        $this->storeManager = $storeManager;
        $this->stockResolver = $stockResolver;
        $this->getStockIdForWebsiteByCode = $getStockIdForWebsiteByCode;
    }

    /**
     * @return int
     */
    public function execute(): int
    {
        $websiteCode = $this->storeManager->getWebsite()->getCode();
        $stockId = $this->getStockIdForWebsiteByCode->execute($websiteCode);

        return $stockId;
    }
}
