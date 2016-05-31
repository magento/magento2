<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Model\Test\Unit\ResourceModel\Type\Db;

use Magento\Framework\App\ResourceConnection\ConnectionAdapterInterface;
use Magento\Framework\DB\LoggerInterface;
use Magento\Framework\Model\ResourceModel\Type\Db\ConnectionFactory;
use Magento\Framework\ObjectManagerInterface;

class ConnectionFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ConnectionFactory
     */
    private $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\ObjectManagerInterface
     */
    private $objectManagerMock;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $this->objectManagerMock = $this->getMockBuilder(ObjectManagerInterface::class)
            ->getMockForAbstractClass();
        $this->model = new ConnectionFactory($this->objectManagerMock);
    }

    /**
     * @return void
     */
    public function testCreateNoActiveConfig()
    {
        $config = ['foo' => 'bar'];

        $loggerMock = $this->getMockBuilder(LoggerInterface::class)
            ->getMockForAbstractClass();

        $connectionAdapterMock = $this->getMockBuilder(ConnectionAdapterInterface::class)
            ->getMockForAbstractClass();

        $this->objectManagerMock
            ->expects($this->once())
            ->method('create')
            ->with(ConnectionAdapterInterface::class, ['config' => $config])
            ->willReturn($connectionAdapterMock);
        $this->objectManagerMock
            ->expects($this->once())
            ->method('get')
            ->with(LoggerInterface::class)
            ->willReturn($loggerMock);

        $connectionAdapterMock
            ->expects($this->once())
            ->method('getConnection')
            ->with($loggerMock)
            ->willReturn('Expected result');

        $this->assertEquals('Expected result', $this->model->create($config));
    }
}
