<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Event;

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
     * @var \Magento\Event\Manager
     */
    protected $_eventManager;

    protected function setUp()
    {
        $this->_invoker = $this->getMock('Magento\Event\InvokerInterface');
        $this->_eventFactory = $this->getMock('Magento\EventFactory', array('create'), array(), '', false);
        $this->_event = $this->getMock('Magento\Event', array(), array(), '', false);
        $this->_wrapperFactory = $this->getMock('Magento\Event\WrapperFactory', array(), array(), '',
            false);
        $this->_observer = $this->getMock('Magento\Event\Observer', array(), array(), '', false);
        $this->_eventConfigMock = $this->getMock('Magento\Event\ConfigInterface');

        $this->_eventManager = new \Magento\Event\Manager(
            $this->_invoker, $this->_eventConfigMock, $this->_eventFactory, $this->_wrapperFactory
        );
    }

    public function testDispatch()
    {
        $data = array('123');

        $this->_event->expects($this->once())->method('setName')->with('some_event')->will($this->returnSelf());
        $this->_eventFactory->expects($this->once())->method('create')->with(array('data' => $data))
            ->will($this->returnValue($this->_event));

        $this->_observer->expects($this->once())->method('setData')
            ->with(array_merge(array('event' => $this->_event), $data))->will($this->returnSelf());
        $this->_wrapperFactory->expects($this->once())->method('create')
            ->will($this->returnValue($this->_observer));
        $this->_invoker->expects($this->once())->method('dispatch')->with(array(
            'instance' => 'class',
            'method' => 'method',
            'name' => 'observer'
        ), $this->_observer);

        $this->_eventConfigMock->expects($this->once())
            ->method('getObservers')
            ->with('some_event')
            ->will($this->returnValue(array(
                'observer' => array('instance' => 'class', 'method' => 'method', 'name' => 'observer')
            )));
        $this->_eventManager->dispatch('some_event', array('123'));
    }

    public function testDispatchWithEmptyEventObservers()
    {
        $this->_eventConfigMock->expects($this->once())
            ->method('getObservers')
            ->with('some_event')
            ->will($this->returnValue(array()));
        $this->_invoker->expects($this->never())->method('dispatch');
        $this->_eventManager->dispatch('some_event');
    }
}
