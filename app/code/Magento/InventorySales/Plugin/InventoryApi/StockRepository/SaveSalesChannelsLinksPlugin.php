<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Plugin\InventoryApi\StockRepository;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Message\ManagerInterface;
use Magento\InventoryApi\Api\Data\StockInterface;
use Magento\InventoryApi\Api\StockRepositoryInterface;
use Magento\InventoryCatalogApi\Api\DefaultStockProviderInterface;
use Magento\InventorySales\Model\SalesChannel;
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
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @param ReplaceSalesChannelsForStockInterface $replaceSalesChannelsOnStock
     * @param LoggerInterface $logger
     * @param GetAssignedSalesChannelsForStockInterface $getAssignedSalesChannelsForStock
     * @param DefaultStockProviderInterface $defaultStockProvider
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        ReplaceSalesChannelsForStockInterface $replaceSalesChannelsOnStock,
        LoggerInterface $logger,
        GetAssignedSalesChannelsForStockInterface $getAssignedSalesChannelsForStock,
        DefaultStockProviderInterface $defaultStockProvider,
        ManagerInterface $messageManager
    ) {
        $this->replaceSalesChannelsOnStock = $replaceSalesChannelsOnStock;
        $this->logger = $logger;
        $this->getAssignedSalesChannelsForStock = $getAssignedSalesChannelsForStock;
        $this->defaultStockProvider = $defaultStockProvider;
        $this->messageManager = $messageManager;
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
        $unAssignedWebsiteCodes = $this->getUnassignedSalesChannelsForStock($stock);

        if (null !== $salesChannels) {
            try {
                $this->replaceSalesChannelsOnStock->execute($salesChannels, $stockId);

                if (count($unAssignedWebsiteCodes)) {
                    $mergedSalesChannels = array_merge(
                        $unAssignedWebsiteCodes,
                        $this->getAssignedSalesChannelsForStock->execute($this->defaultStockProvider->getId())
                    );
                    $this->replaceSalesChannelsOnStock->execute(
                        $mergedSalesChannels,
                        $this->defaultStockProvider->getId()
                    );
                    $this->messageManager->addNoticeMessage(
                        __('All unassigned sales channels will be assigned to the Default Stock')
                    );
                }

            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
                throw new CouldNotSaveException(__('Could not replace Sales Channels for Stock'), $e);
            }
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
            if ($salesChannel->getType() === SalesChannel::TYPE_WEBSITE) {
                $newWebsiteCodes[] = $salesChannel->getCode();
            }
        }

        foreach ($assignedSalesChannels as $salesChannel) {
            if ($salesChannel->getType() === SalesChannel::TYPE_WEBSITE
                && !in_array($salesChannel->getCode(), $newWebsiteCodes, true)
            ) {
                $result[] = $salesChannel;
            }
        }

        return $result;
    }
}
