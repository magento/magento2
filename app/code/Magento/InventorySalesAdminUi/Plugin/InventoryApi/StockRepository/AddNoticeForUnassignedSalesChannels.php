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
use Magento\InventorySales\Model\GetUnassignedSalesChannelsForStock;

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
     * @var GetUnassignedSalesChannelsForStock
     */
    private $getUnassignedSalesChannelsForStock;

    /**
     * @param ManagerInterface $messageManager
     * @param GetUnassignedSalesChannelsForStock $getUnassignedSalesChannelsForStock
     */
    public function __construct(
        ManagerInterface $messageManager,
        GetUnassignedSalesChannelsForStock $getUnassignedSalesChannelsForStock
    ) {
        $this->messageManager = $messageManager;
        $this->getUnassignedSalesChannelsForStock = $getUnassignedSalesChannelsForStock;
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
        $stockId,
        StockInterface $stock
    ): int {
        $extensionAttributes = $stock->getExtensionAttributes();
        $salesChannels = $extensionAttributes->getSalesChannels();
        $unAssignedSalesChannels = $this->getUnassignedSalesChannelsForStock->execute($stock);

        if (null !== $salesChannels && count($unAssignedSalesChannels)) {
            $this->messageManager->addNoticeMessage(
                __('All unassigned sales channels will be assigned to the Default Stock')
            );
        }

        return $stockId;
    }
}
