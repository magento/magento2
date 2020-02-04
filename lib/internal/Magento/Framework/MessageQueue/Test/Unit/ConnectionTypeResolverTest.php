<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Test\Unit;

use Magento\Framework\MessageQueue\ConnectionTypeResolver;
use Magento\Framework\MessageQueue\ConnectionTypeResolverInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * ConnectionTypeResolverTest
 */
class ConnectionTypeResolverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * Verify get connection type method
     *
     * @return void
     */
    public function testGetConnectionType(): void
    {
        $resolverTwo = $this->createMock(ConnectionTypeResolverInterface::class);
        $resolverOne = $this->createMock(ConnectionTypeResolverInterface::class);
        $resolverOne->expects($this->once())
                    ->method('getConnectionType')
                    ->with('test')
                    ->willReturn(null);
        $resolverTwo->expects($this->once())
                    ->method('getConnectionType')
                    ->with('test')
                    ->willReturn('some-type');

        $model = new ConnectionTypeResolver([$resolverOne, $resolverTwo]);
        $this->assertEquals('some-type', $model->getConnectionType('test'));
    }

    /**
     * Verify get connection with empty resolvers
     *
     * @return void
     */
    public function testGetConnectionTypeWithEmptyResolvers(): void
    {
        $this->objectManager = new ObjectManager($this);
        $model = $this->objectManager->getObject(ConnectionTypeResolver::class);
        $this->expectException(\LogicException::class);
        $model->getConnectionType('test');
    }
    /**
     * Verify get Connection types with exception
     *
     * @return void
     */
    public function testGetConnectionTypeWithException(): void
    {
        $resolverOne = $this->createMock(ConnectionTypeResolverInterface::class);
        $resolverTwo = $this->createMock(ConnectionTypeResolverInterface::class);
        $resolverOne->expects($this->once())
                    ->method('getConnectionType')
                    ->with('test')
                    ->willReturn(null);
        $resolverTwo->expects($this->once())
                    ->method('getConnectionType')
                    ->with('test')
                    ->willReturn(null);

        $this->expectException(\LogicException::class);
        $model = new ConnectionTypeResolver([$resolverOne, $resolverTwo]);
        $model->getConnectionType('test');
    }
}
