<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Unit\Model;

use Magento\Analytics\ReportXml\ConnectionFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\Pdo\Mysql as MysqlPdoAdapter;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class ConnectionFactoryTest
 */
class ConnectionFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ResourceConnection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resourceConnectionMock;

    /**
     * @var ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManagerMock;

    /**
     * @var ConnectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $connectionNewMock;

    /**
     * @var AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
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
    protected function setUp()
    {
        $this->resourceConnectionMock = $this->getMockBuilder(ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerMock = $this->getMockBuilder(ObjectManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

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
