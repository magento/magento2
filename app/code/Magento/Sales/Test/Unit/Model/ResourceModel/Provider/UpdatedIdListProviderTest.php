<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\ResourceModel\Provider;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\FlagManager;
use Magento\Sales\Model\ResourceModel\Provider\Query\IdListBuilder;
use Magento\Sales\Model\ResourceModel\Provider\UpdatedIdListProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class UpdatedIdListProviderTest extends TestCase
{
    /**
     * @var ResourceConnection&MockObject
     */
    private $resourceConnection;

    /**
     * @var AdapterInterface&MockObject
     */
    private $connection;

    /**
     * @var IdListBuilder&MockObject
     */
    private $idListBuilder;

    /**
     * @var FlagManager&MockObject
     */
    private $flagManager;

    protected function setUp(): void
    {
        $this->resourceConnection = $this->createMock(ResourceConnection::class);
        $this->connection = $this->createMock(AdapterInterface::class);
        $this->idListBuilder = $this->createMock(IdListBuilder::class);
        $this->flagManager = $this->createMock(FlagManager::class);

        $this->resourceConnection->method('getConnection')
            ->with('sales')
            ->willReturn($this->connection);
        $this->resourceConnection->method('getTableName')
            ->willReturnCallback(static fn(string $table): string => $table);
    }

    public function testGetIdsUsesPersistedCursorRange(): void
    {
        $maxEntitySelect = $this->createMock(Select::class);
        $idSelect = $this->createStub(Select::class);

        $this->connection->expects($this->once())
            ->method('select')
            ->willReturn($maxEntitySelect);
        $maxEntitySelect->expects($this->once())
            ->method('from')
            ->with(['main_table' => 'sales_order'], $this->anything())
            ->willReturnSelf();
        $this->connection->expects($this->once())
            ->method('fetchOne')
            ->with($maxEntitySelect)
            ->willReturn(12000);

        $this->flagManager->expects($this->once())
            ->method('getFlagData')
            ->with('sales_grid_async_last_entity_id_sales_order_grid')
            ->willReturn(10000);
        $this->idListBuilder->expects($this->once())
            ->method('build')
            ->with('sales_order', 'sales_order_grid', 10000, 12000)
            ->willReturn($idSelect);
        $this->connection->expects($this->once())
            ->method('fetchAll')
            ->with($idSelect, [], \Zend_Db::FETCH_COLUMN)
            ->willReturn([11001, 11002]);
        $this->flagManager->expects($this->never())->method('saveFlag');

        $provider = new UpdatedIdListProvider($this->resourceConnection, $this->idListBuilder, $this->flagManager);
        $actual = $provider->getIds('sales_order', 'sales_order_grid');

        self::assertSame([11001, 11002], $actual);
    }

    public function testGetIdsStartsFromTailAndAdvancesOnEmptyRange(): void
    {
        $maxEntitySelect = $this->createMock(Select::class);
        $idSelect = $this->createStub(Select::class);

        $this->connection->expects($this->once())
            ->method('select')
            ->willReturn($maxEntitySelect);
        $maxEntitySelect->expects($this->once())
            ->method('from')
            ->with(['main_table' => 'sales_order'], $this->anything())
            ->willReturnSelf();
        $this->connection->expects($this->once())
            ->method('fetchOne')
            ->with($maxEntitySelect)
            ->willReturn(25000);

        $this->flagManager->expects($this->once())
            ->method('getFlagData')
            ->with('sales_grid_async_last_entity_id_sales_order_grid')
            ->willReturn(null);
        $this->idListBuilder->expects($this->once())
            ->method('build')
            ->with('sales_order', 'sales_order_grid', 15000, 25000)
            ->willReturn($idSelect);
        $this->connection->expects($this->once())
            ->method('fetchAll')
            ->with($idSelect, [], \Zend_Db::FETCH_COLUMN)
            ->willReturn([]);
        $this->flagManager->expects($this->once())
            ->method('saveFlag')
            ->with('sales_grid_async_last_entity_id_sales_order_grid', 25000);

        $provider = new UpdatedIdListProvider($this->resourceConnection, $this->idListBuilder, $this->flagManager);
        $actual = $provider->getIds('sales_order', 'sales_order_grid');

        self::assertSame([], $actual);
    }
}
