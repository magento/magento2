<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\TestFramework\Unit;

/**
 * Framework for unit tests containing helper methods
 *
 * Number of fields is necessary because of the number of fields used by multiple layers
 * of parent classes.
 */
abstract class AbstractFactoryTestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var string
     */
    protected $factoryClassName;

    /**
     * @var string
     */
    protected $instanceClassName;

    /**
     * @var \Magento\Framework\ObjectManagerInterface | \PHPUnit\Framework\MockObject\MockObject
     */
    protected $objectManagerMock;

    /**
     * @var object
     */
    protected $factory;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->objectManager = new Helper\ObjectManager($this);
        $this->objectManagerMock = $this->getMockBuilder(\Magento\Framework\ObjectManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->factory = $this->objectManager->getObject(
            $this->factoryClassName,
            ['objectManager' => $this->objectManagerMock]
        );
    }

    /**
     * @return void
     */
    public function testCreate()
    {
        $instanceMock = $this->getMockBuilder($this->instanceClassName)
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->willReturn($instanceMock);
        $this->assertSame($instanceMock, $this->factory->create());
    }
}
