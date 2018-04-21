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
use Magento\InventorySales\Model\ReplaceSalesChannelsForStockInterface;
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
     * @param ReplaceSalesChannelsForStockInterface $replaceSalesChannelsOnStock
     * @param LoggerInterface $logger
     */
    public function __construct(
        ReplaceSalesChannelsForStockInterface $replaceSalesChannelsOnStock,
        LoggerInterface $logger
    ) {
        $this->replaceSalesChannelsOnStock = $replaceSalesChannelsOnStock;
        $this->logger = $logger;
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
        $stockId,
        StockInterface $stock
    ): int {
        $extensionAttributes = $stock->getExtensionAttributes();
        $salesChannels = $extensionAttributes->getSalesChannels();
        if (null !== $salesChannels) {
            try {
                $this->replaceSalesChannelsOnStock->execute($salesChannels, $stockId);
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
                throw new CouldNotSaveException(__('Could not replace Sales Channels for Stock'), $e);
            }
        }

        return $stockId;
    }
}
