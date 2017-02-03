<?php
/***
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\ObjectManager\Test\Unit\Profiler;

class FactoryDecoratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Name of the base class to wrap in logger
     */
    const CLASS_NAME = 'Magento\Test\Di\WrappedClass';

    /**
     * Name of the wrapper class that does logging
     */
    const LOGGER_NAME = 'Magento\Test\Di\WrappedClass\Logger';

    /**
     * Name of the class that generates wrappers - should not be wrapped by logger
     */
    const GENERATOR_NAME = 'Magento\Framework\ObjectManager\Profiler\Code\Generator\Logger';

    /** @var  \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\ObjectManager\FactoryInterface*/
    private $objectManagerMock;

    /** @var  \Magento\Framework\ObjectManager\Profiler\FactoryDecorator */
    private $model;

    public function setUp()
    {
        require_once __DIR__ . '/../_files/logger_classes.php';
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->objectManagerMock = $this->getMockBuilder('Magento\Framework\ObjectManager\FactoryInterface')
            ->disableOriginalConstructor()
            ->getMock();

        // Instantiate SUT
        $this->model = $objectManager->getObject(
            'Magento\Framework\ObjectManager\Profiler\FactoryDecorator',
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
