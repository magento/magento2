<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\InventoryCatalog\Plugin\Model;

use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\InventoryApi\Api\StockRepositoryInterface;
use Magento\InventoryCatalog\Api\DefaultStockProviderInterface;
use Magento\InventorySales\Model\GetAssignedSalesChannelsForStockInterface;

/**
 * Class provide Before Plugin on StockRepositoryInterface::deleteByItem to prevent default stock could be deleted
 */
class StockRepositoryPlugin
{
    /**
     * @var DefaultStockProviderInterface
     */
    private $defaultStockProvider;

    /**
     * @var GetAssignedSalesChannelsForStockInterface
     */
    private $assignedSalesChannelsForStock;

    /**
     * @param DefaultStockProviderInterface $defaultStockProvider
     * @param GetAssignedSalesChannelsForStockInterface $assignedSalesChannelsForStock
     */
    public function __construct(
        DefaultStockProviderInterface $defaultStockProvider,
        GetAssignedSalesChannelsForStockInterface $assignedSalesChannelsForStock
    ) {
        $this->defaultStockProvider = $defaultStockProvider;
        $this->assignedSalesChannelsForStock = $assignedSalesChannelsForStock;
    }

    /**
     * Prevent default source to be deleted
     *
     * @param StockRepositoryInterface $subject
     * @param int $stockId
     *
     * @return void
     * @throws CouldNotDeleteException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeDeleteById(StockRepositoryInterface $subject, int $stockId)
    {
        if ($stockId === $this->defaultStockProvider->getId()) {
            throw new CouldNotDeleteException(__('Default Stock could not be deleted.'));
        }
        $assignSalesChannels = $this->assignedSalesChannelsForStock->execute($stockId);
        if (count($assignSalesChannels)) {
            throw new CouldNotDeleteException(__('Stock has at least one sale channel and could not be deleted.'));
        }
    }
}
