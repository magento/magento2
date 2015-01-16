<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Event;

/**
 * Class ManagerTest
 *
 * @package Magento\Framework\Event
 */
class ManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_invoker;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_eventFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_event;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_wrapperFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_observer;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_eventConfigMock;

    /**
     * @var \Magento\Framework\Event\Manager
     */
    protected $_eventManager;

    protected function setUp()
    {
        $this->_invoker = $this->getMock('Magento\Framework\Event\InvokerInterface');
        $this->_eventConfigMock = $this->getMock('Magento\Framework\Event\ConfigInterface');

        $this->_eventManager = new \Magento\Framework\Event\Manager($this->_invoker, $this->_eventConfigMock);
    }

    public function testDispatch()
    {
        $this->_eventConfigMock->expects(
            $this->once()
        )->method(
            'getObservers'
        )->with(
            'some_event'
        )->will(
            $this->returnValue(
                ['observer' => ['instance' => 'class', 'method' => 'method', 'name' => 'observer']]
            )
        );
        $this->_eventManager->dispatch('some_event', ['123']);
    }

    public function testDispatchWithEmptyEventObservers()
    {
        $this->_eventConfigMock->expects(
            $this->once()
        )->method(
            'getObservers'
        )->with(
            'some_event'
        )->will(
            $this->returnValue([])
        );
        $this->_invoker->expects($this->never())->method('dispatch');
        $this->_eventManager->dispatch('some_event');
    }
}
