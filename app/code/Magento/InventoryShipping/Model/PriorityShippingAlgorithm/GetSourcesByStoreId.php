<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryShipping\Model\PriorityShippingAlgorithm;

use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventoryApi\Api\GetAssignedSourcesForStockInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\InventorySalesApi\Api\StockResolverInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Retrieve sources related to current stock ordered by priority.
 */
class GetSourcesByStoreId
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var WebsiteRepositoryInterface
     */
    private $websiteRepository;

    /**
     * @var StockResolverInterface
     */
    private $stockResolver;

    /**
     * @var GetAssignedSourcesForStockInterface
     */
    private $getAssignedSourcesForStock;

    /**
     * @param StoreManagerInterface $storeManager
     * @param WebsiteRepositoryInterface $websiteRepository
     * @param StockResolverInterface $stockResolver
     * @param GetAssignedSourcesForStockInterface $getAssignedSourcesForStock
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        WebsiteRepositoryInterface $websiteRepository,
        StockResolverInterface $stockResolver,
        GetAssignedSourcesForStockInterface $getAssignedSourcesForStock
    ) {
        $this->storeManager = $storeManager;
        $this->websiteRepository = $websiteRepository;
        $this->stockResolver = $stockResolver;
        $this->getAssignedSourcesForStock = $getAssignedSourcesForStock;
    }

    /**
     * Returns sources related to current stock ordered by priority.
     *
     * @param int $storeId
     *
     * @return SourceInterface[]
     */
    public function execute(int $storeId): array
    {
        $store = $this->storeManager->getStore($storeId);
        $website = $this->websiteRepository->getById($store->getWebsiteId());
        $stock = $this->stockResolver->get(SalesChannelInterface::TYPE_WEBSITE, $website->getCode());

        return $this->getAssignedSourcesForStock->execute((int)$stock->getStockId());
    }
}
