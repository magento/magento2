<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Test\Unit;

use Magento\Framework\MessageQueue\ConnectionTypeResolverInterface;
use Magento\Framework\MessageQueue\ConnectionTypeResolver;

class ConnectionTypeResolverTest extends \PHPUnit\Framework\TestCase
{
    public function testGetConnectionType()
    {
        $resolverOne = $this->createMock(ConnectionTypeResolverInterface::class);
        $resolverOne->expects($this->once())->method('getConnectionType')->with('test')->willReturn(null);

        $resolverTwo = $this->createMock(ConnectionTypeResolverInterface::class);
        $resolverTwo->expects($this->once())->method('getConnectionType')->with('test')->willReturn('some-type');

        $model = new ConnectionTypeResolver([$resolverOne, $resolverTwo]);
        $this->assertEquals('some-type', $model->getConnectionType('test'));
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Unknown connection name test
     */
    public function testGetConnectionTypeWithException()
    {
        $resolverOne = $this->createMock(ConnectionTypeResolverInterface::class);
        $resolverOne->expects($this->once())->method('getConnectionType')->with('test')->willReturn(null);

        $resolverTwo = $this->createMock(ConnectionTypeResolverInterface::class);
        $resolverTwo->expects($this->once())->method('getConnectionType')->with('test')->willReturn(null);

        $model = new ConnectionTypeResolver([$resolverOne, $resolverTwo]);
        $model->getConnectionType('test');
    }
}
