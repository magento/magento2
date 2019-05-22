<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryExportStock\Model;

use Magento\InventoryExportStockApi\Api\ExportStockIndexDataInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterfaceFactory;

/**
 * Class ExportStockIndexData provides stock index export
 */
class ExportStockIndexData implements ExportStockIndexDataInterface
{
    /**
     * @var SalesChannelInterfaceFactory
     */
    private $salesChannelInterfaceFactory;

    /**
     * @var ExportStockIndexDataBySalesChannel
     */
    private $exportStockIndexDataBySalesChannel;

    /**
     * @param SalesChannelInterfaceFactory $salesChannelInterfaceFactory
     * @param ExportStockIndexDataBySalesChannel $exportStockIndexDataBySalesChannel
     */
    public function __construct(
        SalesChannelInterfaceFactory $salesChannelInterfaceFactory,
        ExportStockIndexDataBySalesChannel $exportStockIndexDataBySalesChannel
    ) {

        $this->salesChannelInterfaceFactory = $salesChannelInterfaceFactory;
        $this->exportStockIndexDataBySalesChannel = $exportStockIndexDataBySalesChannel;
    }

    /**
     * @inheritDoc
     */
    public function execute(string $salesChannelCode): array
    {
        $salesChannel = $this->salesChannelInterfaceFactory->create(
            [
                'data' => [
                    SalesChannelInterface::TYPE => SalesChannelInterface::TYPE_WEBSITE,
                    SalesChannelInterface::CODE => $salesChannelCode
                ]
            ]
        );

        return $this->exportStockIndexDataBySalesChannel->execute($salesChannel);
    }
}
