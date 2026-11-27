<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Store\Test\Unit\App\Config\Source;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Adapter\TableNotFoundException;
use Magento\Framework\DB\Select;
use Magento\Store\App\Config\Source\RuntimeConfigSource;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Verify runtime scopes reads and database availability reporting.
 *
 * @SuppressWarnings(PHPMD.ExcessiveParameterList)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RuntimeConfigSourceTest extends TestCase
{
    /**
     * Deployment configuration mock.
     *
     * @var DeploymentConfig|MockObject
     */
    private $deploymentConfig;

    /**
     * Runtime scopes source under test.
     *
     * @var RuntimeConfigSource
     */
    private $configSource;

    /**
     * Scope table connection mock.
     *
     * @var MockObject
     */
    private $connection;

    /**
     * Database resource mock.
     *
     * @var MockObject
     */
    private $resourceConnection;

    /**
     * Create isolated deployment and database dependencies.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->connection = $this->createMock(AdapterInterface::class);
        $this->resourceConnection = $this->createMock(ResourceConnection::class);
        $this->deploymentConfig = $this->getMockBuilder(DeploymentConfig::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->configSource = new RuntimeConfigSource(
            $this->deploymentConfig,
            $this->resourceConnection
        );
    }

    /**
     * Return empty scope pools without declaring runtime data available.
     *
     * @return void
     */
    public function testGet()
    {
        $this->deploymentConfig->expects($this->any())
            ->method('isDbAvailable')
            ->willReturn(true);
        $this->resourceConnection->expects($this->any())->method('getConnection')->willReturn($this->connection);

        $selectMock = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->getMock();
        $selectMock->expects($this->any())->method('from')->willReturnSelf();
        $this->connection->expects($this->any())->method('select')->willReturn($selectMock);
        $this->connection->expects($this->any())->method('fetchAll')->willReturn([]);
        $this->assertEquals(
            [
                'websites' => [],
                'groups' => [],
                'stores' => [],
            ],
            $this->configSource->get()
        );
        $this->assertFalse($this->configSource->hasRuntimeData());
    }

    /**
     * Clear runtime-data status when the database becomes unavailable.
     *
     * @return void
     */
    public function testRuntimeDataAvailabilityIsReset(): void
    {
        $this->deploymentConfig->method('isDbAvailable')->willReturnOnConsecutiveCalls(true, false);
        $this->resourceConnection->method('getConnection')->willReturn($this->connection);
        $select = $this->createMock(Select::class);
        $select->method('from')->willReturnSelf();
        $this->connection->method('select')->willReturn($select);
        $this->connection->method('fetchAll')->willReturn([
            ['code' => 'default', 'website_id' => 1, 'group_id' => 1, 'store_id' => 1],
        ]);

        $this->configSource->get();
        $this->assertTrue($this->configSource->hasRuntimeData());
        $this->configSource->get();
        $this->assertFalse($this->configSource->hasRuntimeData());
    }

    /**
     * Clear runtime-data status between requests.
     *
     * @return void
     */
    public function testRequestResetClearsRuntimeDataAvailability(): void
    {
        $this->deploymentConfig->method('isDbAvailable')->willReturn(true);
        $this->resourceConnection->method('getConnection')->willReturn($this->connection);
        $select = $this->createMock(Select::class);
        $select->method('from')->willReturnSelf();
        $this->connection->method('select')->willReturn($select);
        $this->connection->method('fetchAll')->willReturn([
            ['code' => 'default', 'website_id' => 1, 'group_id' => 1, 'store_id' => 1],
        ]);

        $this->configSource->get();
        $this->assertTrue($this->configSource->hasRuntimeData());
        $this->configSource->_resetState();
        $this->assertFalse($this->configSource->hasRuntimeData());
    }

    /**
     * Avoid opening a connection when no database is configured.
     *
     * @return void
     */
    public function testGetWhenDbIsNotAvailable()
    {
        $this->deploymentConfig->expects($this->once())
            ->method('isDbAvailable')
            ->willReturn(false);
        $this->resourceConnection->expects($this->never())
            ->method('getConnection');

        $this->assertEquals([], $this->configSource->get());
        $this->assertFalse($this->configSource->hasRuntimeData());
    }

    /**
     * Treat missing scope tables as unavailable runtime data.
     *
     * @return void
     */
    public function testGetWhenDbIsEmpty()
    {
        $this->deploymentConfig->expects($this->once())
            ->method('isDbAvailable')
            ->willReturn(true);
        $this->connection->method('fetchAll')
            ->willThrowException($this->createMock(TableNotFoundException::class));
        $selectMock = $this->createMock(Select::class);
        $selectMock->method('from')
            ->willReturnSelf();
        $this->connection->method('select')
            ->willReturn($selectMock);
        $this->resourceConnection->expects($this->once())
            ->method('getConnection')
            ->willReturn($this->connection);

        $this->assertEquals([], $this->configSource->get());
        $this->assertFalse($this->configSource->hasRuntimeData());
    }
}
