<?php
/**
 * Framework for unit tests containing helper methods
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Number of fields is necessary because of the number of fields used by multiple layers
 * of parent classes.
 *
 */
namespace Magento\Framework\TestFramework\Unit;

/**
 * Class \Magento\Framework\TestFramework\Unit\AbstractFactoryTestCase
 *
 */
abstract class AbstractFactoryTestCase extends \PHPUnit_Framework_TestCase
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
     * @var \Magento\Framework\ObjectManagerInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;

    /**
     * @var object
     */
    protected $factory;

    /**
     * Setup function
     * @return void
     */
    protected function setUp()
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
     * Test create
     * @return void
     */
    public function testCreate()
    {
        $instanceMock = $this->getMockBuilder($this->instanceClassName)
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($instanceMock));
        $this->assertSame($instanceMock, $this->factory->create());
    }
}
