<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Plugin\InventoryApi\StockRepository;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\InventoryApi\Api\Data\StockInterface;
use Magento\InventoryApi\Api\StockRepositoryInterface;
use Magento\InventoryCatalogApi\Api\DefaultStockProviderInterface;
use Magento\InventorySales\Model\GetUnassignedSalesChannelsForStock;
use Magento\InventorySalesApi\Model\GetAssignedSalesChannelsForStockInterface;
use Magento\InventorySalesApi\Model\ReplaceSalesChannelsForStockInterface;
use Psr\Log\LoggerInterface;

/**
 * Save Sales Channels Links for Stock on Save method of StockRepositoryInterface
 */
class SaveSalesChannelsLinksPlugin
{
    /**
     * @var ReplaceSalesChannelsForStockInterface
     */
    private $replaceSalesChannelsOnStock;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var GetAssignedSalesChannelsForStockInterface
     */
    private $getAssignedSalesChannelsForStock;

    /**
     * @var DefaultStockProviderInterface
     */
    private $defaultStockProvider;

    /**
     * @var GetUnassignedSalesChannelsForStock
     */
    private $getUnassignedSalesChannelsForStock;

    /**
     * @param ReplaceSalesChannelsForStockInterface $replaceSalesChannelsOnStock
     * @param LoggerInterface $logger
     * @param GetAssignedSalesChannelsForStockInterface $getAssignedSalesChannelsForStock
     * @param DefaultStockProviderInterface $defaultStockProvider
     * @param GetUnassignedSalesChannelsForStock $getUnassignedSalesChannelsForStock
     */
    public function __construct(
        ReplaceSalesChannelsForStockInterface $replaceSalesChannelsOnStock,
        LoggerInterface $logger,
        GetAssignedSalesChannelsForStockInterface $getAssignedSalesChannelsForStock,
        DefaultStockProviderInterface $defaultStockProvider,
        GetUnassignedSalesChannelsForStock $getUnassignedSalesChannelsForStock
    ) {
        $this->replaceSalesChannelsOnStock = $replaceSalesChannelsOnStock;
        $this->logger = $logger;
        $this->getAssignedSalesChannelsForStock = $getAssignedSalesChannelsForStock;
        $this->defaultStockProvider = $defaultStockProvider;
        $this->getUnassignedSalesChannelsForStock = $getUnassignedSalesChannelsForStock;
    }

    /**
     * Saves Sales Channel Link for Stock
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
        $extensionAttributes = $stock->getExtensionAttributes();
        $salesChannels = $extensionAttributes->getSalesChannels() ?: [];
        $unAssignedSalesChannels = $this->getUnassignedSalesChannelsForStock->execute($stock);

        try {
            $this->replaceSalesChannelsOnStock->execute($salesChannels, $stockId);

            if (count($unAssignedSalesChannels)) {
                $mergedSalesChannels = array_merge(
                    $unAssignedSalesChannels,
                    $this->getAssignedSalesChannelsForStock->execute($this->defaultStockProvider->getId())
                );
                $this->replaceSalesChannelsOnStock->execute(
                    $mergedSalesChannels,
                    $this->defaultStockProvider->getId()
                );
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw new CouldNotSaveException(__('Could not replace Sales Channels for Stock'), $e);
        }

        return $stockId;
    }
}
