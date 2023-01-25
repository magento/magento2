<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Test\Unit\ResourceConnection;

use Magento\Framework\App\ResourceConnection\ConnectionAdapterInterface;
use Magento\Framework\App\ResourceConnection\ConnectionFactory;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Adapter\DdlCache;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConnectionFactoryTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var ConnectionFactory
     */
    private $connectionFactory;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    private $objectManagerMock;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->objectManagerMock = $this->getMockBuilder(ObjectManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->connectionFactory = $this->objectManager->getObject(
            ConnectionFactory::class,
            ['objectManager' => $this->objectManagerMock]
        );
    }

    public function testCreate()
    {
        $cacheAdapterMock = $this->getMockBuilder(DdlCache::class)
            ->disableOriginalConstructor()
            ->getMock();
        $adapterClass = ConnectionAdapterInterface::class;
        $connectionAdapterMock = $this->getMockBuilder($adapterClass)
            ->disableOriginalConstructor()
            ->getMock();
        $connectionMock = $this->getMockBuilder(AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $connectionMock->expects($this->once())
            ->method('setCacheAdapter')
            ->with($cacheAdapterMock)
            ->willReturnSelf();
        $connectionAdapterMock->expects($this->once())
            ->method('getConnection')
            ->willReturn($connectionMock);
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with(ConnectionAdapterInterface::class)
            ->willReturn($connectionAdapterMock);
        $this->objectManagerMock->expects($this->any())
            ->method('get')
            ->with(DdlCache::class)
            ->willReturn($cacheAdapterMock);
        $this->assertSame($connectionMock, $this->connectionFactory->create(['active' => true]));
    }
}
