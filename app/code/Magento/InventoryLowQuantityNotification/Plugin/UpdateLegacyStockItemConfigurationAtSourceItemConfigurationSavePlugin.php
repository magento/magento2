<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryLowQuantityNotification\Plugin;

use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\InventoryLowQuantityNotificationApi\Api\Data\SourceItemConfigurationInterface;
use Magento\InventoryLowQuantityNotificationApi\Api\SourceItemConfigurationsSaveInterface;
use Magento\InventoryCatalogApi\Api\DefaultSourceProviderInterface;
use Magento\InventoryCatalogApi\Api\DefaultStockProviderInterface;
use Magento\InventoryCatalogApi\Model\GetProductIdsBySkusInterface;
use Magento\InventoryCatalogApi\Model\IsSingleSourceModeInterface;

class UpdateLegacyStockItemConfigurationAtSourceItemConfigurationSavePlugin
{
    /**
     * @var IsSingleSourceModeInterface
     */
    private $isSingleSourceMode;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var DefaultSourceProviderInterface
     */
    private $defaultSourceProvider;

    /**
     * @var DefaultStockProviderInterface
     */
    private $defaultStockProvider;

    /**
     * @var GetProductIdsBySkusInterface
     */
    private $getProductIdsBySkus;

    /**
     * @param IsSingleSourceModeInterface $isSingleSourceMode
     * @param ResourceConnection $resourceConnection
     * @param DefaultSourceProviderInterface $defaultSourceProvider
     * @param DefaultStockProviderInterface $defaultStockProvider
     * @param GetProductIdsBySkusInterface $getProductIdsBySkus
     */
    public function __construct(
        IsSingleSourceModeInterface $isSingleSourceMode,
        ResourceConnection $resourceConnection,
        DefaultSourceProviderInterface $defaultSourceProvider,
        DefaultStockProviderInterface $defaultStockProvider,
        GetProductIdsBySkusInterface $getProductIdsBySkus
    ) {
        $this->isSingleSourceMode = $isSingleSourceMode;
        $this->resourceConnection = $resourceConnection;
        $this->defaultSourceProvider = $defaultSourceProvider;
        $this->defaultStockProvider = $defaultStockProvider;
        $this->getProductIdsBySkus = $getProductIdsBySkus;
    }

    /**
     * @param SourceItemConfigurationsSaveInterface $subject
     * @param $result
     * @param SourceItemConfigurationInterface[] $sourceItemConfigurations
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecute(
        SourceItemConfigurationsSaveInterface $subject,
        $result,
        array $sourceItemConfigurations
    ): void {

        if ($this->isSingleSourceMode->execute()) {
            return;
        }

        $connection = $this->resourceConnection->getConnection();
        $connection->beginTransaction();

        try {
            foreach ($sourceItemConfigurations as $sourceItemConfiguration) {
                if ($sourceItemConfiguration->getSourceCode() !== $this->defaultSourceProvider->getCode()) {
                    continue;
                }

                $productId = $this->getProductIdsBySkus->execute(
                    [$sourceItemConfiguration->getSku()]
                )[$sourceItemConfiguration->getSku()];

                if ($sourceItemConfiguration->getNotifyStockQty() === null) {
                    $data[StockItemInterface::USE_CONFIG_NOTIFY_STOCK_QTY] = 1;
                } else {
                    $data[StockItemInterface::USE_CONFIG_NOTIFY_STOCK_QTY] = 0;
                    $data[StockItemInterface::NOTIFY_STOCK_QTY] = $sourceItemConfiguration->getNotifyStockQty();
                }

                $where = [
                    StockItemInterface::STOCK_ID . ' = ?' => $this->defaultStockProvider->getId(),
                    StockItemInterface::PRODUCT_ID . ' = ?' => $productId
                ];

                $connection->update(
                    $this->resourceConnection->getTableName('cataloginventory_stock_item'),
                    $data,
                    $where
                );
            }

            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();
            throw $e;
        }
    }
}
