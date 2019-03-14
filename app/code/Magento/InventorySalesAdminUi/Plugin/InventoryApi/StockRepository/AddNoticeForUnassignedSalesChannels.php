<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySalesAdminUi\Plugin\InventoryApi\StockRepository;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Message\ManagerInterface;
use Magento\InventoryApi\Api\Data\StockInterface;
use Magento\InventoryApi\Api\StockRepositoryInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\InventorySalesApi\Model\GetAssignedSalesChannelsForStockInterface;

/**
 * Add notice message when when sales channels unassigned from stock
 * on Save method of StockRepositoryInterface
 */
class AddNoticeForUnassignedSalesChannels
{
    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var GetAssignedSalesChannelsForStockInterface
     */
    private $getAssignedSalesChannelsForStock;

    /**
     * @param ManagerInterface $messageManager
     * @param GetAssignedSalesChannelsForStockInterface $getAssignedSalesChannelsForStock
     */
    public function __construct(
        ManagerInterface $messageManager,
        GetAssignedSalesChannelsForStockInterface $getAssignedSalesChannelsForStock
    ) {
        $this->messageManager = $messageManager;
        $this->getAssignedSalesChannelsForStock = $getAssignedSalesChannelsForStock;
    }

    /**
     * Add notice message if we have unassigned sales channels
     *
     * @param StockRepositoryInterface $subject
     * @param int $stockId
     * @param StockInterface $stock
     * @return int
     * @throws CouldNotSaveException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSave(
        StockRepositoryInterface $subject,
        int $stockId,
        StockInterface $stock
    ): int {
        $unAssignedSalesChannels = $this->getUnassignedSalesChannelsForStock($stock);

        if (count($unAssignedSalesChannels)) {
            $this->messageManager->addNoticeMessage(
                __('All unassigned sales channels will be assigned to the Default Stock')
            );
        }

        return $stockId;
    }

    /**
     * Return all sales channels witch will be unassigned from the saved stock
     *
     * @param StockInterface $stock
     * @return \Magento\InventorySales\Model\SalesChannel[]
     */
    private function getUnassignedSalesChannelsForStock(StockInterface $stock): array
    {
        $newWebsiteCodes = $result = [];
        $assignedSalesChannels = $this->getAssignedSalesChannelsForStock->execute((int)$stock->getStockId());
        $extensionAttributes = $stock->getExtensionAttributes();
        $newSalesChannels = $extensionAttributes->getSalesChannels() ?: [];

        foreach ($newSalesChannels as $salesChannel) {
            if ($salesChannel->getType() === SalesChannelInterface::TYPE_WEBSITE) {
                $newWebsiteCodes[] = $salesChannel->getCode();
            }
        }

        foreach ($assignedSalesChannels as $salesChannel) {
            if ($salesChannel->getType() === SalesChannelInterface::TYPE_WEBSITE
                && !in_array($salesChannel->getCode(), $newWebsiteCodes, true)
            ) {
                $result[] = $salesChannel;
            }
        }

        return $result;
    }
}
