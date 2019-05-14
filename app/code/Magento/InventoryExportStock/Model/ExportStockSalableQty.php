<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryExportStock\Model;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\InventoryExportStockApi\Api\Data\ExportStockSalableQtySearchResultInterface;
use Magento\InventoryExportStockApi\Api\ExportStockSalableQtyBySalesChannelInterface;
use Magento\InventoryExportStockApi\Api\ExportStockSalableQtyInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterfaceFactory;

/**
 * Class ExportStockSalableQty provides product stock information by search criteria
 */
class ExportStockSalableQty implements ExportStockSalableQtyInterface
{
    /**
     * @var SalesChannelInterfaceFactory
     */
    private $salesChannelInterfaceFactory;

    /**
     * @var ExportStockSalableQtyBySalesChannelInterface
     */
    private $exportStockSalableQtyBySalesChannel;

    /**
     * @param SalesChannelInterfaceFactory $salesChannelInterfaceFactory
     * @param ExportStockSalableQtyBySalesChannelInterface $exportStockSalableQtyBySalesChannel
     */
    public function __construct(
        SalesChannelInterfaceFactory $salesChannelInterfaceFactory,
        ExportStockSalableQtyBySalesChannelInterface $exportStockSalableQtyBySalesChannel
    ) {

        $this->salesChannelInterfaceFactory = $salesChannelInterfaceFactory;
        $this->exportStockSalableQtyBySalesChannel = $exportStockSalableQtyBySalesChannel;
    }

    /**
     * @inheritDoc
     */
    public function getList(
        string $salesChannelCode,
        SearchCriteriaInterface $searchCriteria
    ): ExportStockSalableQtySearchResultInterface {
        $salesChannel = $this->salesChannelInterfaceFactory->create(
            [
                'data' => [
                    SalesChannelInterface::TYPE => SalesChannelInterface::TYPE_WEBSITE,
                    SalesChannelInterface::CODE => $salesChannelCode
                ]
            ]
        );

        return $this->exportStockSalableQtyBySalesChannel->getList($salesChannel, $searchCriteria);
    }
}
