<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
 * @SuppressWarnings(PHPMD.ExcessiveParameterList)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RuntimeConfigSourceTest extends TestCase
{
    /**
     * @var DeploymentConfig|MockObject
     */
    private $deploymentConfig;

    /**
     * @var RuntimeConfigSource
     */
    private $configSource;

    /**
     * @var MockObject
     */
    private $connection;

    /**
     * @var MockObject
     */
    private $resourceConnection;

    protected function setUp(): void
    {
        $this->connection = $this->getMockForAbstractClass(AdapterInterface::class);
        $this->resourceConnection = $this->createMock(ResourceConnection::class);
        $this->deploymentConfig = $this->getMockBuilder(DeploymentConfig::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->configSource = new RuntimeConfigSource(
            $this->deploymentConfig,
            $this->resourceConnection
        );
    }

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
    }

    public function testGetWhenDbIsNotAvailable()
    {
        $this->deploymentConfig->expects($this->once())
            ->method('isDbAvailable')
            ->willReturn(false);
        $this->resourceConnection->expects($this->never())
            ->method('getConnection');

        $this->assertEquals([], $this->configSource->get());
    }

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
    }
}
