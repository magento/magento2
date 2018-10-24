<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Model;

use Magento\InventoryApi\Api\Data\StockInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\InventorySalesApi\Api\StockResolverInterface;
use Magento\InventorySalesApi\Model\StockByWebsiteIdResolverInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;

/**
 * @inheritdoc
 */
class StockByWebsiteIdResolver implements StockByWebsiteIdResolverInterface
{
    /**
     * @var WebsiteRepositoryInterface
     */
    private $websiteRepository;

    /**
     * @var StockResolverInterface
     */
    private $stockResolver;

    /**
     * @param WebsiteRepositoryInterface $websiteRepository
     * @param StockResolverInterface $stockResolver
     */
    public function __construct(
        WebsiteRepositoryInterface $websiteRepository,
        StockResolverInterface $stockResolver
    ) {
        $this->websiteRepository = $websiteRepository;
        $this->stockResolver = $stockResolver;
    }

    /**
     * @inheritdoc
     */
    public function execute(int $websiteId): StockInterface
    {
        $websiteCode = $this->websiteRepository->getById($websiteId)->getCode();

        return $this->stockResolver->execute(
            SalesChannelInterface::TYPE_WEBSITE,
            $websiteCode
        );
    }
}
