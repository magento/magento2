<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryExportStock\Model;

use Magento\InventoryExportStockApi\Api\ExportStockIndexDataInterface;
use Magento\InventoryExportStockApi\Api\ExportStockIndexDataBySalesChannelInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterfaceFactory;

/**
 * @inheritDoc
 */
class ExportStockIndexData implements ExportStockIndexDataInterface
{
    /**
     * @var SalesChannelInterfaceFactory
     */
    private $salesChannelInterfaceFactory;

    /**
     * @var ExportStockIndexDataBySalesChannelInterface
     */
    private $exportStockIndexDataBySalesChannel;

    /**
     * @param SalesChannelInterfaceFactory $salesChannelInterfaceFactory
     * @param ExportStockIndexDataBySalesChannelInterface $exportStockIndexDataBySalesChannel
     */
    public function __construct(
        SalesChannelInterfaceFactory $salesChannelInterfaceFactory,
        ExportStockIndexDataBySalesChannelInterface $exportStockIndexDataBySalesChannel
    ) {

        $this->salesChannelInterfaceFactory = $salesChannelInterfaceFactory;
        $this->exportStockIndexDataBySalesChannel = $exportStockIndexDataBySalesChannel;
    }

    /**
     * @inheritDoc
     */
    public function execute(string $salesChannelType, string $salesChannelCode): array
    {
        $salesChannel = $this->salesChannelInterfaceFactory->create(
            [
                'data' => [
                    SalesChannelInterface::TYPE => $salesChannelType,
                    SalesChannelInterface::CODE => $salesChannelCode
                ]
            ]
        );

        return $this->exportStockIndexDataBySalesChannel->execute($salesChannel);
    }
}
