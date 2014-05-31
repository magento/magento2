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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
                array('observer' => array('instance' => 'class', 'method' => 'method', 'name' => 'observer'))
            )
        );
        $this->_eventManager->dispatch('some_event', array('123'));
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
            $this->returnValue(array())
        );
        $this->_invoker->expects($this->never())->method('dispatch');
        $this->_eventManager->dispatch('some_event');
    }
}
