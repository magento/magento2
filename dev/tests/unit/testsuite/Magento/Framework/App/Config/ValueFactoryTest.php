<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Config;

class ValueFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Framework\App\Config\ValueFactory
     */
    protected $model;

    /**
     * @var \Magento\Framework\ObjectManagerInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;

    protected function setUp()
    {
        $this->objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->objectManagerMock = $this->getMockBuilder('Magento\Framework\ObjectManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->model = $this->objectManager->getObject(
            'Magento\Framework\App\Config\ValueFactory',
            ['objectManager' => $this->objectManagerMock]
        );
    }

    public function testCreate()
    {
        $instanceMock = $this->getMockBuilder('Magento\Framework\App\Config\ValueInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($instanceMock));
        $this->assertSame($instanceMock, $this->model->create());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCreateWithException()
    {
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue('somethingElse'));
        $this->model->create();
    }
}
