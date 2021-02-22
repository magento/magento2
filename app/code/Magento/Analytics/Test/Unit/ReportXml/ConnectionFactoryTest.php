<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Unit\ReportXml;

use Magento\Analytics\ReportXml\ConnectionFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\Pdo\Mysql as MysqlPdoAdapter;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class ConnectionFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ResourceConnection|\PHPUnit\Framework\MockObject\MockObject
     */
    private $resourceConnectionMock;

    /**
     * @var ObjectManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $objectManagerMock;

    /**
     * @var ConnectionFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $connectionNewMock;

    /**
     * @var AdapterInterface|\PHPUnit\Framework\MockObject\MockObject
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
        $this->resourceConnectionMock = $this->getMockBuilder(ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerMock = $this->getMockBuilder(ObjectManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->connectionMock = $this->getMockBuilder(MysqlPdoAdapter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->connectionNewMock = $this->getMockBuilder(MysqlPdoAdapter::class)
            ->disableOriginalConstructor()
            ->getMock();

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
