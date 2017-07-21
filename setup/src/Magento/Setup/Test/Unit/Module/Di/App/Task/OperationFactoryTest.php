<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Setup\Test\Unit\Module\Di\App\Task;

use Magento\Setup\Module\Di\App\Task\OperationException;
use Magento\Setup\Module\Di\App\Task\OperationFactory;

class OperationFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var OperationFactory
     */
    private $factory;

    /**
     * @var \Magento\Framework\ObjectManagerInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManagerMock;

    protected function setUp()
    {
        $this->objectManagerMock = $this->getMockBuilder(\Magento\Framework\ObjectManagerInterface::class)
            ->setMethods([])
            ->getMock();
        $objectManagerProviderMock = $this->getMock(\Magento\Setup\Model\ObjectManagerProvider::class, [], [], '', false);
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
        $operationInstance = $this->getMockBuilder(\Magento\Setup\Module\Di\App\Task\OperationInterface::class)
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
        $this->setExpectedException(
            \Magento\Setup\Module\Di\App\Task\OperationException::class,
            sprintf('Unrecognized operation "%s"', $notRegisteredOperation),
            OperationException::UNAVAILABLE_OPERATION
        );
        $this->factory->create($notRegisteredOperation);
    }

    /**
     * @return array
     */
    public function aliasesDataProvider()
    {
        return  [
            [OperationFactory::AREA_CONFIG_GENERATOR, [], \Magento\Setup\Module\Di\App\Task\Operation\Area::class],
            [OperationFactory::INTERCEPTION, null, \Magento\Setup\Module\Di\App\Task\Operation\Interception::class],
            [OperationFactory::INTERCEPTION_CACHE, 1, \Magento\Setup\Module\Di\App\Task\Operation\InterceptionCache::class],
        ];
    }
}
