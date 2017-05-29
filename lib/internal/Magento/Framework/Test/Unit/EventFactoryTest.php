<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Test\Unit;

class EventFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\EventFactory
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_objectManagerMock;

    /**
     * @var \Magento\Framework\Event
     */
    protected $_expectedObject;

    protected function setUp()
    {
        $this->_objectManagerMock = $this->getMock(\Magento\Framework\ObjectManagerInterface::class);
        $this->_model = new \Magento\Framework\EventFactory($this->_objectManagerMock);
        $this->_expectedObject = $this->getMockBuilder(\Magento\Framework\Event::class)->getMock();
    }

    public function testCreate()
    {
        $arguments = ['property' => 'value'];
        $this->_objectManagerMock->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            \Magento\Framework\Event::class,
            $arguments
        )->will(
            $this->returnValue($this->_expectedObject)
        );

        $this->assertEquals($this->_expectedObject, $this->_model->create($arguments));
    }
}
