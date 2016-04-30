<?php
/**
 * Framework for unit tests containing helper methods
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Number of fields is necessary because of the number of fields used by multiple layers
 * of parent classes.
 *
 */
namespace Magento\Framework\TestFramework\Unit;

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

    protected function setUp()
    {
        $this->objectManager = new Helper\ObjectManager($this);
        $this->objectManagerMock = $this->getMockBuilder('Magento\Framework\ObjectManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->factory = $this->objectManager->getObject(
            $this->factoryClassName,
            ['objectManager' => $this->objectManagerMock]
        );
    }

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
