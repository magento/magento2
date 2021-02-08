<?php
/***
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\ObjectManager\Test\Unit\Profiler;

class FactoryDecoratorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Name of the base class to wrap in logger
     */
    const CLASS_NAME = \Magento\Test\Di\WrappedClass::class;

    /**
     * Name of the wrapper class that does logging
     */
    const LOGGER_NAME = \Magento\Test\Di\WrappedClass\Logger::class;

    /**
     * Name of the class that generates wrappers - should not be wrapped by logger
     */
    const GENERATOR_NAME = \Magento\Framework\ObjectManager\Profiler\Code\Generator\Logger::class;

    /** @var  \PHPUnit\Framework\MockObject\MockObject | \Magento\Framework\ObjectManager\FactoryInterface*/
    private $objectManagerMock;

    /** @var  \Magento\Framework\ObjectManager\Profiler\FactoryDecorator */
    private $model;

    protected function setUp(): void
    {
        require_once __DIR__ . '/../_files/logger_classes.php';
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->objectManagerMock = $this->getMockBuilder(\Magento\Framework\ObjectManager\FactoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        // Instantiate SUT
        $this->model = $objectManager->getObject(
            \Magento\Framework\ObjectManager\Profiler\FactoryDecorator::class,
            ['subject' => $this->objectManagerMock]
        );
    }

    public function testCreate()
    {
        $baseObjectName = self::CLASS_NAME;
        $baseObject = new $baseObjectName();

        $arguments = [1, 2, 3];

        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with(self::CLASS_NAME, $arguments)
            ->willReturn($baseObject);

        $this->assertInstanceOf(self::LOGGER_NAME, $this->model->create(self::CLASS_NAME, $arguments));
    }

    public function testCreateNeglectGenerator()
    {
        $arguments = [1, 2, 3];
        $loggerMock = $this->getMockBuilder(self::GENERATOR_NAME)->disableOriginalConstructor()->getMock();

        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with(self::GENERATOR_NAME, $arguments)
            ->willReturn($loggerMock);

        $this->assertSame($loggerMock, $this->model->create(self::GENERATOR_NAME, $arguments));
    }
}
