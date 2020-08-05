<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Model\Test\Unit\ResourceModel\Type\Db;

use Magento\Framework\App\ResourceConnection\ConnectionAdapterInterface;
use Magento\Framework\Model\ResourceModel\Type\Db\ConnectionFactory;
use Magento\Framework\ObjectManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConnectionFactoryTest extends TestCase
{
    /**
     * @var ConnectionFactory
     */
    private $connectionFactory;

    /**
     * @var MockObject|ObjectManagerInterface
     */
    private $objectManagerMock;

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
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
