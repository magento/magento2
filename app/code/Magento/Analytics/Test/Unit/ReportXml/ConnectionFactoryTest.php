<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Analytics\Test\Unit\ReportXml;

use Magento\Analytics\ReportXml\ConnectionFactory;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\ResourceConnection\ConfigInterface as ResourceConfigInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Model\ResourceModel\Type\Db\ConnectionFactoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConnectionFactoryTest extends TestCase
{
    /**
     * @var ResourceConfigInterface|MockObject
     */
    private $resourceConfigMock;

    /**
     * @var DeploymentConfig|MockObject
     */
    private $deploymentConfigMock;

    /**
     * @var ConnectionFactoryInterface|MockObject
     */
    private $connectionFactoryMock;

    /**
     * @var ConnectionFactory
     */
    private $connectionFactory;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->resourceConfigMock = $this->createMock(ResourceConfigInterface::class);
        $this->deploymentConfigMock = $this->createMock(DeploymentConfig::class);
        $this->connectionFactoryMock = $this->createMock(ConnectionFactoryInterface::class);

        $this->connectionFactory = new ConnectionFactory(
            $this->resourceConfigMock,
            $this->deploymentConfigMock,
            $this->connectionFactoryMock
        );
    }

    public function testGetConnection()
    {
        $resourceName = 'default';

        $this->resourceConfigMock->expects($this->once())
            ->method('getConnectionName')
            ->with($resourceName)
            ->willReturn('default');
        $this->deploymentConfigMock->expects($this->once())
            ->method('get')
            ->with('db/connection/default')
            ->willReturn(['host' => 'localhost', 'port' => 3306, 'persistent' => true]);
        $connectionMock = $this->createMock(AdapterInterface::class);
        $this->connectionFactoryMock->expects($this->once())
            ->method('create')
            ->with(['host' => 'localhost', 'port' => 3306, 'use_buffered_query' => false])
            ->willReturn($connectionMock);

        $connection = $this->connectionFactory->getConnection($resourceName);
        $this->assertSame($connectionMock, $connection);
    }
}
