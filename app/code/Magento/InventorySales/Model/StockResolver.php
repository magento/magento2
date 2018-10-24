<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Model;

use Magento\InventoryApi\Api\Data\StockInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterfaceFactory;
use Magento\InventorySalesApi\Api\GetStockBySalesChannelInterface;
use Magento\InventorySalesApi\Api\StockResolverInterface;

/**
 * @inheritdoc
 */
class StockResolver implements StockResolverInterface
{
    /**
     * @var GetStockBySalesChannelInterface
     */
    private $getStockBySalesChannel;

    /**
     * @var SalesChannelInterfaceFactory
     */
    private $salesChannelInterfaceFactory;

    /**
     * @param GetStockBySalesChannelInterface $getStockBySalesChannel
     * @param SalesChannelInterfaceFactory $salesChannelInterfaceFactory
     */
    public function __construct(
        GetStockBySalesChannelInterface $getStockBySalesChannel,
        SalesChannelInterfaceFactory $salesChannelInterfaceFactory
    ) {
        $this->getStockBySalesChannel = $getStockBySalesChannel;
        $this->salesChannelInterfaceFactory = $salesChannelInterfaceFactory;
    }

    /**
     * @inheritdoc
     */
    public function execute(string $type, string $code): StockInterface
    {
        $salesChannel = $this->salesChannelInterfaceFactory->create([
            'data' => [
                SalesChannelInterface::TYPE => $type,
                SalesChannelInterface::CODE => $code
            ]
        ]);
        return $this->getStockBySalesChannel->execute($salesChannel);
    }
}
