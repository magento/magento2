<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Model\Test\Unit\ResourceModel\Type\Db;

use Magento\Framework\App\ResourceConnection\ConnectionAdapterInterface;
use Magento\Framework\Model\ResourceModel\Type\Db\ConnectionFactory;
use Magento\Framework\ObjectManagerInterface;

class ConnectionFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ConnectionFactory
     */
    private $connectionFactory;

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
        $this->connectionFactory = new ConnectionFactory($this->objectManagerMock);
    }

    /**
     * @return void
     */
    public function testCreateNoActiveConfig()
    {
        $config = ['foo' => 'bar'];

        $connectionAdapterMock = $this->getMockBuilder(ConnectionAdapterInterface::class)
            ->getMockForAbstractClass();

        $this->objectManagerMock
            ->expects($this->once())
            ->method('create')
            ->with(ConnectionAdapterInterface::class, ['config' => $config])
            ->willReturn($connectionAdapterMock);

        $connectionAdapterMock
            ->expects($this->once())
            ->method('getConnection')
            ->willReturn('Expected result');

        $this->assertEquals('Expected result', $this->connectionFactory->create($config));
    }
}
