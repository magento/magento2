<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Module\Di\App\Task;

use Magento\Framework\ObjectManagerInterface;
use Magento\Setup\Model\ObjectManagerProvider;
use Magento\Setup\Module\Di\App\Task\Operation\Area;
use Magento\Setup\Module\Di\App\Task\Operation\Interception;
use Magento\Setup\Module\Di\App\Task\Operation\InterceptionCache;
use Magento\Setup\Module\Di\App\Task\OperationException;
use Magento\Setup\Module\Di\App\Task\OperationFactory;
use Magento\Setup\Module\Di\App\Task\OperationInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class OperationFactoryTest extends TestCase
{
    /**
     * @var OperationFactory
     */
    private $factory;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    private $objectManagerMock;

    protected function setUp(): void
    {
        $this->objectManagerMock = $this->getMockBuilder(ObjectManagerInterface::class)
            ->getMockForAbstractClass();
        $objectManagerProviderMock = $this->createMock(ObjectManagerProvider::class);
        $objectManagerProviderMock->expects($this->once())->method('get')->willReturn($this->objectManagerMock);
        $this->factory = new OperationFactory(
            $objectManagerProviderMock
        );
    }

    /**
     * @param string $alias
     * @param mixed $arguments
     * @dataProvider aliasesDataProvider
     */
    public function testCreateSuccess($alias, $arguments, $instanceName)
    {
        $operationInstance = $this->getMockBuilder(OperationInterface::class)
            ->getMock();

        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with($instanceName, ['data' => $arguments])
            ->willReturn($operationInstance);

        $this->assertSame($operationInstance, $this->factory->create($alias, $arguments));
    }

    public function testCreateException()
    {
        $notRegisteredOperation = 'coffee';
        $this->expectException(OperationException::class);
        $this->expectExceptionMessage(
            sprintf('Unrecognized operation "%s"', $notRegisteredOperation)
        );
        $this->factory->create($notRegisteredOperation);
    }

    /**
     * @return array
     */
    public static function aliasesDataProvider()
    {
        return  [
            [OperationFactory::AREA_CONFIG_GENERATOR, [], Area::class],
            [OperationFactory::INTERCEPTION, null, Interception::class],
            [
                OperationFactory::INTERCEPTION_CACHE,
                1,
                InterceptionCache::class
            ],
        ];
    }
}
