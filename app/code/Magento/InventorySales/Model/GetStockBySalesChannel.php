<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\InventoryApi\Api\Data\StockInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\InventorySalesApi\Api\GetStockBySalesChannelInterface;
use Magento\InventoryApi\Api\StockRepositoryInterface;
use Magento\InventorySales\Model\ResourceModel\StockIdResolver;
use Magento\InventorySales\Model\StockBySalesChannelCache;

/**
 * @inheritdoc
 */
class GetStockBySalesChannel implements GetStockBySalesChannelInterface
{
    /**
     * @var StockRepositoryInterface
     */
    private $stockRepository;

    /**
     * @var StockIdResolver
     */
    private $stockIdResolver;

    /**
     * @var \Magento\InventorySales\Model\StockBySalesChannelCache
     */
    private $stockBySalesChannelCache;

    /**
     * @param StockRepositoryInterface $stockRepositoryInterface
     * @param StockIdResolver $stockIdResolver
     * @param \Magento\InventorySales\Model\StockBySalesChannelCache $stockBySalesChannelCache
     */
    public function __construct(
        StockRepositoryInterface $stockRepositoryInterface,
        StockIdResolver $stockIdResolver,
        StockBySalesChannelCache $stockBySalesChannelCache
    ) {
        $this->stockRepository = $stockRepositoryInterface;
        $this->stockIdResolver = $stockIdResolver;
        $this->stockBySalesChannelCache = $stockBySalesChannelCache;
    }

    /**
     * @inheritdoc
     */
    public function execute(SalesChannelInterface $salesChannel): StockInterface
    {
        $cachedStock = $this->stockBySalesChannelCache->get($salesChannel->getCode(), $salesChannel->getType());

        if (null !== $cachedStock) {
            return $cachedStock;
        }

        $stockId = $this->stockIdResolver->resolve(
            $salesChannel->getType(),
            $salesChannel->getCode()
        );

        if (null === $stockId) {
            throw new NoSuchEntityException(__('No linked stock found'));
        }

        $this->stockBySalesChannelCache->set(
            $salesChannel->getCode(),
            $salesChannel->getType(),
            $this->stockRepository->get($stockId)
        );

        return $this->stockBySalesChannelCache->get($salesChannel->getCode(), $salesChannel->getType());
    }
}
