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
     * @param StockRepositoryInterface $stockRepositoryInterface
     * @param StockIdResolver $stockIdResolver
     */
    public function __construct(
        StockRepositoryInterface $stockRepositoryInterface,
        StockIdResolver $stockIdResolver
    ) {
        $this->stockRepository = $stockRepositoryInterface;
        $this->stockIdResolver = $stockIdResolver;
    }

    /**
     * @inheritdoc
     */
    public function execute(SalesChannelInterface $salesChannel): StockInterface
    {
        $stockId = $this->stockIdResolver->resolve(
            $salesChannel->getType(),
            $salesChannel->getCode()
        );

        if (null === $stockId) {
            throw new NoSuchEntityException(__('No linked stock found'));
        }
        return $this->stockRepository->get($stockId);
    }
}
