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
 * @since 2.0.0
 */
abstract class AbstractFactoryTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     * @since 2.0.0
     */
    protected $objectManager;

    /**
     * @var string
     * @since 2.0.0
     */
    protected $factoryClassName;

    /**
     * @var string
     * @since 2.0.0
     */
    protected $instanceClassName;

    /**
     * @var \Magento\Framework\ObjectManagerInterface | \PHPUnit_Framework_MockObject_MockObject
     * @since 2.0.0
     */
    protected $objectManagerMock;

    /**
     * @var object
     * @since 2.0.0
     */
    protected $factory;

    /**
     * Setup function
     * @return void
     * @since 2.0.0
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
     * @since 2.0.0
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
