<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Model;

use Magento\InventorySales\Model\ResourceModel\GetAssignedSalesChannelsDataForStock;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterfaceFactory;
use Magento\InventorySalesApi\Model\GetAssignedSalesChannelsForStockInterface;

/**
 * @inheritdoc
 */
class GetAssignedSalesChannelsForStock implements GetAssignedSalesChannelsForStockInterface
{
    /**
     * @var GetAssignedSalesChannelsDataForStock
     */
    private $getAssignedSalesChannelsDataForStock;

    /**
     * @var SalesChannelInterfaceFactory
     */
    private $salesChannelFactory;

    /**
     * @param GetAssignedSalesChannelsDataForStock $getAssignedSalesChannelsDataForStock
     * @param SalesChannelInterfaceFactory $salesChannelFactory
     */
    public function __construct(
        GetAssignedSalesChannelsDataForStock $getAssignedSalesChannelsDataForStock,
        SalesChannelInterfaceFactory $salesChannelFactory
    ) {
        $this->getAssignedSalesChannelsDataForStock = $getAssignedSalesChannelsDataForStock;
        $this->salesChannelFactory = $salesChannelFactory;
    }

    /**
     * @inheritdoc
     */
    public function execute(int $stockId): array
    {
        $salesChannelsData = $this->getAssignedSalesChannelsDataForStock->execute($stockId);

        $salesChannels = [];
        foreach ($salesChannelsData as $salesChannelData) {
            /** @var SalesChannelInterface $salesChannel */
            $salesChannel = $this->salesChannelFactory->create();
            $salesChannel->setType($salesChannelData[SalesChannelInterface::TYPE]);
            $salesChannel->setCode($salesChannelData[SalesChannelInterface::CODE]);
            $salesChannels[] = $salesChannel;
        }
        return $salesChannels;
    }
}
