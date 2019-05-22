<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryExportStock\Model;

use Magento\InventoryExportStock\Model\ResourceModel\StockIndexDumpProcessor;
use Magento\InventoryExportStockApi\Api\ExportStockIndexDataInterface;
use Magento\InventorySales\Model\ResourceModel\GetWebsiteIdByWebsiteCode;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterfaceFactory;
use Magento\InventorySalesApi\Api\GetStockBySalesChannelInterface;

/**
 * Class ExportStockIndexData provides stock index export based on raw data contained in the stock index.
 */
class ExportStockIndexData implements ExportStockIndexDataInterface
{
    /**
     * @var SalesChannelInterfaceFactory
     */
    private $salesChannelInterfaceFactory;

    /**
     * @var StockIndexDumpProcessor
     */
    private $stockIndexDumpProcessor;

    /**
     * @var GetWebsiteIdByWebsiteCode
     */
    private $getWebsiteIdByWebsiteCode;

    /**
     * @var ProductStockIndexDataMapper
     */
    private $productStockIndexDataMapper;

    /**
     * @var GetStockBySalesChannelInterface
     */
    private $getStockBySalesChannel;

    /**
     * @param SalesChannelInterfaceFactory $salesChannelInterfaceFactory
     * @param StockIndexDumpProcessor $stockIndexDumpProcessor
     * @param GetWebsiteIdByWebsiteCode $getWebsiteIdByWebsiteCode
     * @param ProductStockIndexDataMapper $productStockIndexDataMapper
     * @param GetStockBySalesChannelInterface $getStockBySalesChannel
     */
    public function __construct(
        SalesChannelInterfaceFactory $salesChannelInterfaceFactory,
        StockIndexDumpProcessor $stockIndexDumpProcessor,
        GetWebsiteIdByWebsiteCode $getWebsiteIdByWebsiteCode,
        ProductStockIndexDataMapper $productStockIndexDataMapper,
        GetStockBySalesChannelInterface $getStockBySalesChannel
    ) {
        $this->salesChannelInterfaceFactory = $salesChannelInterfaceFactory;
        $this->stockIndexDumpProcessor = $stockIndexDumpProcessor;
        $this->getWebsiteIdByWebsiteCode = $getWebsiteIdByWebsiteCode;
        $this->productStockIndexDataMapper = $productStockIndexDataMapper;
        $this->getStockBySalesChannel = $getStockBySalesChannel;
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

        $stock = $this->getStockBySalesChannel->execute($salesChannel);
        $websiteId = $this->getWebsiteIdByWebsiteCode->execute($salesChannel->getCode());
        $items = $this->stockIndexDumpProcessor->execute($websiteId, $stock->getStockId());
        $productsData = [];
        foreach ($items as $item) {
            $productsData[] = $this->productStockIndexDataMapper->execute($item);
        }

        return $productsData;
    }
}
