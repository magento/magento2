<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Analytics\Test\Unit\ReportXml;

use Magento\Analytics\ReportXml\ConnectionFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Adapter\Pdo\Mysql as MysqlPdoAdapter;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConnectionFactoryTest extends TestCase
{
    /**
     * @var ResourceConnection|MockObject
     */
    private $resourceConnectionMock;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    private $objectManagerMock;

    /**
     * @var ConnectionFactory|MockObject
     */
    private $connectionNewMock;

    /**
     * @var AdapterInterface|MockObject
     */
    private $connectionMock;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var ConnectionFactory
     */
    private $connectionFactory;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->resourceConnectionMock = $this->createMock(ResourceConnection::class);

        $this->objectManagerMock = $this->getMockForAbstractClass(ObjectManagerInterface::class);

        $this->connectionMock = $this->createMock(MysqlPdoAdapter::class);

        $this->connectionNewMock = $this->createMock(MysqlPdoAdapter::class);

        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->connectionFactory = $this->objectManagerHelper->getObject(
            ConnectionFactory::class,
            [
                'resourceConnection' => $this->resourceConnectionMock,
                'objectManager' => $this->objectManagerMock,
            ]
        );
    }

    public function testGetConnection()
    {
        $connectionName = 'read';

        $this->resourceConnectionMock
            ->expects($this->once())
            ->method('getConnection')
            ->with($connectionName)
            ->willReturn($this->connectionMock);

        $this->connectionMock
            ->expects($this->once())
            ->method('getConfig')
            ->with()
            ->willReturn(['persistent' => 1]);

        $this->objectManagerMock
            ->expects($this->once())
            ->method('create')
            ->with(get_class($this->connectionMock), ['config' => ['use_buffered_query' => false]])
            ->willReturn($this->connectionNewMock);

        $this->assertSame($this->connectionNewMock, $this->connectionFactory->getConnection($connectionName));
    }
}
