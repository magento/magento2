<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Plugin\InventoryApi\StockRepository;

use Magento\Framework\Exception\StateException;
use Magento\InventoryApi\Api\Data\StockInterface;
use Magento\InventoryApi\Api\StockRepositoryInterface;
use Magento\InventorySalesApi\Model\GetAssignedSalesChannelsForStockInterface;
use Psr\Log\LoggerInterface;

/**
 * Load Sales Channels for Stock on Get method of StockRepositoryInterface
 */
class LoadSalesChannelsOnGetPlugin
{
    /**
     * @var GetAssignedSalesChannelsForStockInterface
     */
    private $getAssignedSalesChannelsForStock;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param GetAssignedSalesChannelsForStockInterface $getAssignedSalesChannelsForStock
     * @param LoggerInterface $logger
     */
    public function __construct(
        GetAssignedSalesChannelsForStockInterface $getAssignedSalesChannelsForStock,
        LoggerInterface $logger
    ) {
        $this->getAssignedSalesChannelsForStock = $getAssignedSalesChannelsForStock;
        $this->logger = $logger;
    }

    /**
     * Enrich the given Stock Object with the assigned sales channel entities
     *
     * @param StockRepositoryInterface $subject
     * @param StockInterface $stock
     * @return StockInterface
     * @throws StateException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGet(StockRepositoryInterface $subject, StockInterface $stock): StockInterface
    {
        try {
            $salesChannels = $this->getAssignedSalesChannelsForStock->execute((int)$stock->getStockId());

            $extensionAttributes = $stock->getExtensionAttributes();
            $extensionAttributes->setSalesChannels($salesChannels);
            $stock->setExtensionAttributes($extensionAttributes);
            return $stock;
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw new StateException(__('Could not load Sales Channels for Stock'), $e);
        }
    }
}
