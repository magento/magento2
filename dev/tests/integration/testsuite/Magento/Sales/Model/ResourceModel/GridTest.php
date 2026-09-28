<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Model\ResourceModel;

use Magento\Framework\ObjectManagerInterface;
use Magento\Sales\Model\Grid\LastUpdateTimeCache;
use Magento\Sales\Model\ResourceModel\Provider\UpdatedAtListProvider;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Magento\Framework\DB\Adapter\Pdo\Mysql;

/**
 * @magentoDataFixture Magento/Sales/_files/order_with_invoice_shipment_creditmemo.php
 */
class GridTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var LastUpdateTimeCache
     */
    private $lastUpdateTimeCache;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->lastUpdateTimeCache = $this->objectManager->get(LastUpdateTimeCache::class);
    }

    /**
     * @param array $constructorArgs
     * @param string $orderIdField
     */
    #[DataProvider('gridDataProvider')]
    public function testRefreshBySchedule(array $constructorArgs, string $orderIdField)
    {
        $constructorArgs['orderIdField'] = $constructorArgs['mainTableName'] . '.' . $orderIdField;
        $constructorArgs['columns'] = [
            $orderIdField => $constructorArgs['orderIdField'],
            'created_at' => $constructorArgs['mainTableName'] . '.created_at',
            'updated_at' => $constructorArgs['mainTableName'] . '.updated_at',
        ];
        $constructorArgs['notSyncedDataProvider'] = $this->objectManager->get(UpdatedAtListProvider::class);
        $grid = $this->objectManager->create(Grid::class, $constructorArgs);

        $order = $this->objectManager->create(\Magento\Sales\Model\Order::class)
            ->loadByIncrementId('100000111');
        $connection = $grid->getConnection();
        $select = $connection->select()
            ->from($constructorArgs['mainTableName'], ['created_at', 'updated_at'])
            ->where($orderIdField, $order->getEntityId());
        $data = $connection->fetchRow($select);
        $this->assertNotEmpty($data);

        //refresh data without cached updated_at
        $this->lastUpdateTimeCache->remove($constructorArgs['gridTableName']);
        $this->assertEmpty($this->lastUpdateTimeCache->get($constructorArgs['gridTableName']));
        sleep(1);
        $stamp = $this->formatUtcDateTime();
        $data['created_at'] = $data['updated_at'] = $stamp;
        $connection->update(
            $constructorArgs['mainTableName'],
            $data,
            sprintf('%s = %d', $orderIdField, $order->getEntityId())
        );
        // Age past UTC(now-1s) cutoff used inside refreshBySchedule / getIdsWithCutoff.
        sleep(2);
        $indexerProjection = $this->fetchGridIndexerProjection(
            $grid,
            $constructorArgs['mainTableName'],
            $orderIdField,
            (int)$order->getEntityId()
        );
        $this->assertNotEmpty($indexerProjection);
        $cutoff = (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))
            ->sub(new \DateInterval('PT1S'))
            ->format('Y-m-d H:i:s');
        $grid->refreshBySchedule();

        $select = $connection->select()
            ->from($constructorArgs['gridTableName'], ['created_at', 'updated_at'])
            ->where($orderIdField, $order->getEntityId());
        $gridData = $connection->fetchRow($select);
        $this->assertGridTimestampsRoughlyMatch($cutoff, $indexerProjection, $gridData);

        //refresh data with cached updated_at
        $this->assertNotEmpty($this->lastUpdateTimeCache->get($constructorArgs['gridTableName']));
        sleep(1);
        $stamp = $this->formatUtcDateTime();
        $data['created_at'] = $data['updated_at'] = $stamp;
        $connection->update(
            $constructorArgs['mainTableName'],
            $data,
            sprintf('%s = %d', $orderIdField, $order->getEntityId())
        );
        // Age past UTC(now-1s) cutoff used inside refreshBySchedule / getIdsWithCutoff.
        sleep(2);
        $indexerProjection = $this->fetchGridIndexerProjection(
            $grid,
            $constructorArgs['mainTableName'],
            $orderIdField,
            (int)$order->getEntityId()
        );
        $this->assertNotEmpty($indexerProjection);
        $cutoff = (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))
            ->sub(new \DateInterval('PT1S'))
            ->format('Y-m-d H:i:s');
        $grid->refreshBySchedule();

        $select = $connection->select()
            ->from($constructorArgs['gridTableName'], ['created_at', 'updated_at'])
            ->where($orderIdField, $order->getEntityId());
        $gridData = $connection->fetchRow($select);
        $this->assertGridTimestampsRoughlyMatch($cutoff, $indexerProjection, $gridData);
    }

    /**
     * Grid stamps updated_at with UTC(now - 1s) computed inside refresh; tests compute cutoff outside first,
     * so clocks can disagree by one second. created_at mirrors main via indexer SELECT but TIMESTAMP storage
     * can shift reads by one second vs PHP-format expectations.
     *
     * @param string $approxCutoffUtc
     * @param array $indexerProjection
     * @param mixed $gridData
     * @return void
     */
    private function assertGridTimestampsRoughlyMatch(
        string $approxCutoffUtc,
        array $indexerProjection,
        mixed $gridData
    ): void {
        $this->assertIsArray($gridData);
        $this->assertArrayHasKey('created_at', $gridData);
        $this->assertArrayHasKey('updated_at', $gridData);

        $slackSeconds = 2.0;

        $this->assertEqualsWithDelta(
            strtotime($approxCutoffUtc),
            strtotime((string)$gridData['updated_at']),
            $slackSeconds,
            'Grid updated_at should reflect UTC cutoff (± slack across refresh boundary).'
        );
        $this->assertEqualsWithDelta(
            strtotime((string)$indexerProjection['created_at']),
            strtotime((string)$gridData['created_at']),
            $slackSeconds,
            'Grid created_at should mirror indexer projection within TIMESTAMP slack.'
        );
    }

    /**
     * Timestamps Grid::refreshBySchedule pulls from main (same SELECT as indexer, before cutoff overwrite).
     *
     * @param Grid $grid
     * @param string $mainTableName
     * @param string $idField Bare column present in data provider (entity_id/order_id etc.)
     * @param int $entityId Identifier value matched against $mainTableName.$idField
     * @return array
     */
    private function fetchGridIndexerProjection(
        Grid $grid,
        string $mainTableName,
        string $idField,
        int $entityId
    ): array {
        $connection = $grid->getConnection();
        $row = $connection->fetchRow(
            $grid->getGridOriginSelect()->where(
                sprintf('%s.%s = ?', $mainTableName, $idField),
                $entityId
            )
        );

        return \is_array($row) ? $row : [];
    }

    /**
     * Same clock basis as \Magento\Sales\Model\ResourceModel\Grid::refreshBySchedule (UTC).
     *
     * @return string
     */
    private function formatUtcDateTime(): string
    {
        return (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))->format('Y-m-d H:i:s');
    }

    /**
     * @return array
     */
    public static function gridDataProvider(): array
    {
        return [
            'Magento\Sales\Model\ResourceModel\Order\Grid' => [
                [
                    'mainTableName' => 'sales_order',
                    'gridTableName' => 'sales_order_grid',
                ],
                'entity_id',
            ],
            'ShipmentGridAggregator' => [
                [
                    'mainTableName' => 'sales_shipment',
                    'gridTableName' => 'sales_shipment_grid',
                ],
                'order_id',
            ],
            'CreditmemoGridAggregator' => [
                [
                    'mainTableName' => 'sales_creditmemo',
                    'gridTableName' => 'sales_creditmemo_grid',
                ],
                'order_id',
            ],
            'Magento\Sales\Model\ResourceModel\Order\Invoice\Grid' => [
                [
                    'mainTableName' => 'sales_invoice',
                    'gridTableName' => 'sales_invoice_grid',
                ],
                'order_id',
            ],
        ];
    }

    /**
     * @param array $constructorArgs
     * @param string $orderIdField
     * @param string $orderIdIndex
     */
    #[DataProvider('shipmentGridDataProvider')]
    public function testSalesShipmentGridOrderIdFieldIndex(
        array $constructorArgs,
        string $orderIdField,
        string $orderIdIndex
    ) {
        $constructorArgs['orderIdField'] = $constructorArgs['mainTableName'] . '.' . $orderIdField;
        $constructorArgs['columns'] = [
            $orderIdField => $constructorArgs['orderIdField'],
            'created_at' => $constructorArgs['mainTableName'] . '.created_at',
            'updated_at' => $constructorArgs['mainTableName'] . '.updated_at',
        ];
        $constructorArgs['notSyncedDataProvider'] = $this->objectManager->get(UpdatedAtListProvider::class);
        $grid = $this->objectManager->create(Grid::class, $constructorArgs);
        /** @var Mysql $connection */
        $connection = $grid->getConnection();
        $order = $this->objectManager->create(\Magento\Sales\Model\Order::class)
            ->loadByIncrementId('100000111');
        $select = $connection->select()
            ->from($constructorArgs['gridTableName'], ['order_id'])
            ->where("$orderIdField = ?", $order->getEntityId());
        $gridTableIndexes = $connection->getIndexList($constructorArgs['gridTableName']);
        $gridFieldData = $connection->fetchRow($select);
        $testFiledData = ['order_id' => $order->getEntityId()];
        $this->assertEquals($testFiledData, $gridFieldData);
        $this->assertArrayHasKey($orderIdIndex, $gridTableIndexes);
        $this->assertEquals($gridTableIndexes[$orderIdIndex]['fields'][0], $orderIdField);
    }

    /**
     * @return array
     */
    public static function shipmentGridDataProvider(): array
    {
        return [
            'Magento\Sales\Model\ResourceModel\Grid' => [
                [
                    'mainTableName' => 'sales_shipment',
                    'gridTableName' => 'sales_shipment_grid',
                ],
                'order_id',
                'SALES_SHIPMENT_GRID_ORDER_ID'
            ],
        ];
    }
}
