<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\MessageQueue\Test\Unit;

use Magento\Framework\MessageQueue\ConnectionTypeResolver;
use Magento\Framework\MessageQueue\ConnectionTypeResolverInterface;
use PHPUnit\Framework\TestCase;

class ConnectionTypeResolverTest extends TestCase
{
    public function testGetConnectionType()
    {
        $resolverOne = $this->getMockForAbstractClass(ConnectionTypeResolverInterface::class);
        $resolverOne->expects($this->once())->method('getConnectionType')->with('test')->willReturn(null);

        $resolverTwo = $this->getMockForAbstractClass(ConnectionTypeResolverInterface::class);
        $resolverTwo->expects($this->once())->method('getConnectionType')->with('test')->willReturn('some-type');

        $model = new ConnectionTypeResolver([$resolverOne, $resolverTwo]);
        $this->assertEquals('some-type', $model->getConnectionType('test'));
    }

    public function testGetConnectionTypeWithException()
    {
        $this->expectException('LogicException');
        $this->expectExceptionMessage('Unknown connection name test');
        $resolverOne = $this->getMockForAbstractClass(ConnectionTypeResolverInterface::class);
        $resolverOne->expects($this->once())->method('getConnectionType')->with('test')->willReturn(null);

        $resolverTwo = $this->getMockForAbstractClass(ConnectionTypeResolverInterface::class);
        $resolverTwo->expects($this->once())->method('getConnectionType')->with('test')->willReturn(null);

        $model = new ConnectionTypeResolver([$resolverOne, $resolverTwo]);
        $model->getConnectionType('test');
    }
}
