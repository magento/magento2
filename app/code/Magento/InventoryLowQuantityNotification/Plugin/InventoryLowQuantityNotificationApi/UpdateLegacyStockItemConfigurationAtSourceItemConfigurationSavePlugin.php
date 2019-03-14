<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryLowQuantityNotification\Plugin\InventoryLowQuantityNotificationApi;

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

        $sourceItemsConfigurationToUpdate = [];
        $skus = [];
        foreach ($sourceItemConfigurations as $sourceItemConfiguration) {
            if ($sourceItemConfiguration->getSourceCode() !== $this->defaultSourceProvider->getCode()) {
                continue;
            }
            $skus[] = $sourceItemConfiguration->getSku();

            $notifyStockQty = $sourceItemConfiguration->getNotifyStockQty();
            $sourceItemConfigurationData[StockItemInterface::USE_CONFIG_NOTIFY_STOCK_QTY] = $notifyStockQty ? 0 : 1;
            $sourceItemConfigurationData[StockItemInterface::NOTIFY_STOCK_QTY] = $notifyStockQty ?? 1;
            $sourceItemsConfigurationToUpdate[$sourceItemConfiguration->getSku()] = $sourceItemConfigurationData;
        }

        if (empty($sourceItemsConfigurationToUpdate)) {
            return;
        }

        $productIds = $this->getProductIdsBySkus->execute($skus);

        foreach ($sourceItemsConfigurationToUpdate as $sku => &$sourceItemConfiguration) {
            if (!empty($productIds[$sku])) {
                $sourceItemConfiguration[StockItemInterface::PRODUCT_ID] = $productIds[$sku];
            } else {
                unset($sourceItemsConfigurationToUpdate[$sku]);
            }
        }

        $connection = $this->resourceConnection->getConnection();

        $tableName = $this->resourceConnection
            ->getTableName('cataloginventory_stock_item');

        $columnsSql = $this->buildColumnsSqlPart([
            StockItemInterface::STOCK_ID,
            StockItemInterface::PRODUCT_ID,
            StockItemInterface::NOTIFY_STOCK_QTY,
            StockItemInterface::USE_CONFIG_NOTIFY_STOCK_QTY
        ]);

        $valuesSql = $this->buildValuesSqlPart($sourceItemsConfigurationToUpdate);
        $onDuplicateSql = $this->buildOnDuplicateSqlPart([
            StockItemInterface::NOTIFY_STOCK_QTY,
            StockItemInterface::USE_CONFIG_NOTIFY_STOCK_QTY
        ]);
        $bind = $this->getSqlBindData($sourceItemsConfigurationToUpdate);

        $insertSql = sprintf(
            'INSERT INTO %s (%s) VALUES %s %s',
            $tableName,
            $columnsSql,
            $valuesSql,
            $onDuplicateSql
        );
        $connection->query($insertSql, $bind);
    }

    /**
     * @param array $columns
     * @return string
     */
    private function buildColumnsSqlPart(array $columns): string
    {
        $connection = $this->resourceConnection->getConnection();
        $processedColumns = array_map([$connection, 'quoteIdentifier'], $columns);
        $sql = implode(', ', $processedColumns);
        return $sql;
    }

    /**
     * @param array $sourceItemConfigurations
     * @return string
     */
    private function buildValuesSqlPart(array $sourceItemConfigurations): string
    {
        $sql = rtrim(str_repeat('(?, ?, ?, ?), ', count($sourceItemConfigurations)), ', ');
        return $sql;
    }

    /**
     * @param array $sourceItemConfigurations
     * @return array
     */
    private function getSqlBindData(array $sourceItemConfigurations): array
    {
        $bind = [];
        foreach ($sourceItemConfigurations as $sourceItemConfiguration) {
            $bind = array_merge($bind, [
                $this->defaultStockProvider->getId(),
                $sourceItemConfiguration[StockItemInterface::PRODUCT_ID],
                $sourceItemConfiguration[StockItemInterface::NOTIFY_STOCK_QTY],
                $sourceItemConfiguration[StockItemInterface::USE_CONFIG_NOTIFY_STOCK_QTY],
            ]);
        }
        return $bind;
    }

    /**
     * @param array $fields
     * @return string
     */
    private function buildOnDuplicateSqlPart(array $fields): string
    {
        $connection = $this->resourceConnection->getConnection();
        $processedFields = [];
        foreach ($fields as $field) {
            $processedFields[] = sprintf('%1$s = VALUES(%1$s)', $connection->quoteIdentifier($field));
        }
        $sql = 'ON DUPLICATE KEY UPDATE ' . implode(', ', $processedFields);
        return $sql;
    }
}
