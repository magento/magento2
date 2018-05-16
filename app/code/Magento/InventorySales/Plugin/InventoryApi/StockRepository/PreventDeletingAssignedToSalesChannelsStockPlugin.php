<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Plugin\InventoryApi\StockRepository;

use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\InventoryApi\Api\StockRepositoryInterface;
use Magento\InventorySalesApi\Model\GetAssignedSalesChannelsForStockInterface;

/**
 * Prevent deleting of Stock which assigned at least one Sales Channel
 */
class PreventDeletingAssignedToSalesChannelsStockPlugin
{
    /**
     * @var GetAssignedSalesChannelsForStockInterface
     */
    private $assignedSalesChannelsForStock;

    /**
     * @param GetAssignedSalesChannelsForStockInterface $assignedSalesChannelsForStock
     */
    public function __construct(
        GetAssignedSalesChannelsForStockInterface $assignedSalesChannelsForStock
    ) {
        $this->assignedSalesChannelsForStock = $assignedSalesChannelsForStock;
    }

    /**
     * Prevent deleting of Stock which assigned at least one Sales Channel
     *
     * @param StockRepositoryInterface $subject
     * @param int $stockId
     * @return void
     * @throws CouldNotDeleteException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeDeleteById(StockRepositoryInterface $subject, int $stockId)
    {
        $assignSalesChannels = $this->assignedSalesChannelsForStock->execute($stockId);
        if (count($assignSalesChannels)) {
            throw new CouldNotDeleteException(__('Stock has at least one sale channel and could not be deleted.'));
        }
    }
}
