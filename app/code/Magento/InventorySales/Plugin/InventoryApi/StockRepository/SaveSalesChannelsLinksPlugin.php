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
use Magento\InventorySales\Model\SalesChannel;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterfaceFactory;
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
     * @var SalesChannelInterfaceFactory
     */
    private $salesChannelFactory;

    /**
     * @param ReplaceSalesChannelsForStockInterface $replaceSalesChannelsOnStock
     * @param LoggerInterface $logger
     * @param GetAssignedSalesChannelsForStockInterface $getAssignedSalesChannelsForStock
     * @param DefaultStockProviderInterface $defaultStockProvider
     * @param SalesChannelInterfaceFactory $salesChannelInterfaceFactory
     */
    public function __construct(
        ReplaceSalesChannelsForStockInterface $replaceSalesChannelsOnStock,
        LoggerInterface $logger,
        GetAssignedSalesChannelsForStockInterface $getAssignedSalesChannelsForStock,
        DefaultStockProviderInterface $defaultStockProvider,
        SalesChannelInterfaceFactory $salesChannelFactory
    ) {
        $this->replaceSalesChannelsOnStock = $replaceSalesChannelsOnStock;
        $this->logger = $logger;
        $this->getAssignedSalesChannelsForStock = $getAssignedSalesChannelsForStock;
        $this->defaultStockProvider = $defaultStockProvider;
        $this->salesChannelFactory = $salesChannelFactory;
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
        $unAssignedWebsiteCodes = $this->getUnassignedWebsiteCodesForStock($stock);

        if (null !== $salesChannels) {
            try {
                $this->replaceSalesChannelsOnStock->execute($salesChannels, $stockId);

                if (count($unAssignedWebsiteCodes)) {
                    $mergedSalesChannels = array_merge(
                        $unAssignedWebsiteCodes,
                        $this->getAssignedToDefaultWebsiteCodes()
                    );

                    foreach ($mergedSalesChannels as $salesChannelCode) {
                        /** @var SalesChannelInterface $salesChannel */
                        $salesChannel = $this->salesChannelFactory->create();
                        $salesChannel->setType(SalesChannel::TYPE_WEBSITE);
                        $salesChannel->setCode($salesChannelCode);
                        $newSalesChannels[] = $salesChannel;
                    }

                    $this->replaceSalesChannelsOnStock->execute(
                        $newSalesChannels,
                        $this->defaultStockProvider->getId()
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
     * @param StockInterface $stock
     * @return array
     */
    private function getUnassignedWebsiteCodesForStock(StockInterface $stock): array
    {
        $assignedWebsiteCodes = $newWebsiteCodes = [];
        $assignedSalesChannels = $this->getAssignedSalesChannelsForStock->execute((int)$stock->getStockId());

        foreach ($assignedSalesChannels as $salesChannel) {
            if ($salesChannel->getType() === SalesChannel::TYPE_WEBSITE) {
                $assignedWebsiteCodes[] = $salesChannel->getCode();
            }
        }

        $extensionAttributes = $stock->getExtensionAttributes();
        $newSalesChannels = $extensionAttributes->getSalesChannels() ?: [];

        foreach ($newSalesChannels as $salesChannel) {
            if ($salesChannel->getType() === SalesChannel::TYPE_WEBSITE) {
                $newWebsiteCodes[] = $salesChannel->getCode();
            }
        }

        return array_diff($assignedWebsiteCodes, $newWebsiteCodes);
    }

    /**
     * Return website codes assigned to default stock
     *
     * @return array
     */
    private function getAssignedToDefaultWebsiteCodes(): array
    {
        $result = [];
        $assignedToDefaultStockSalesChannels = $this->getAssignedSalesChannelsForStock
            ->execute($this->defaultStockProvider->getId());

        foreach ($assignedToDefaultStockSalesChannels as $salesChannel) {
            $result[] = $salesChannel->getCode();
        }

        return $result;
    }
}
