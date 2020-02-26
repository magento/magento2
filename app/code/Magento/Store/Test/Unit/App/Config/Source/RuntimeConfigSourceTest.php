<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Test\Unit\App\Config\Source;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Store\App\Config\Source\RuntimeConfigSource;

/**
 * @SuppressWarnings(PHPMD.ExcessiveParameterList)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RuntimeConfigSourceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var DeploymentConfig|\PHPUnit_Framework_MockObject_MockObject
     */
    private $deploymentConfig;

    /**
     * @var RuntimeConfigSource
     */
    private $configSource;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $connection;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $resourceConnection;

    public function setUp()
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

    public function testGet()
    {
        $this->deploymentConfig->expects($this->any())
            ->method('get')
            ->with('db')
            ->willReturn(true);
        $this->resourceConnection->expects($this->any())->method('getConnection')->willReturn($this->connection);

        $selectMock = $this->getMockBuilder(Select::class)->disableOriginalConstructor()->getMock();
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

    public function testGenWhenDbNotAvailable()
    {
        $this->deploymentConfig->expects($this->once())
            ->method('get')
            ->with('db')
            ->willReturn(false);
        $this->resourceConnection->expects($this->never())->method('getConnection');

        $this->assertEquals([], $this->configSource->get());
    }
}
