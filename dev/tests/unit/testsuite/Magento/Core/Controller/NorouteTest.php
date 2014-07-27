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
namespace Magento\Core\Controller;

class NorouteTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Core\Controller\Noroute
     */
    protected $_controller;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_requestMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_viewMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_statusMock;

    protected function setUp()
    {
        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_requestMock = $this->getMock('Magento\Framework\App\Request\Http', array(), array(), '', false);
        $this->_viewMock = $this->getMock('\Magento\Framework\App\ViewInterface');
        $this->_statusMock = $this->getMock('Magento\Framework\Object', array('getLoaded'), array(), '', false);
        $this->_controller = $helper->getObject(
            'Magento\Core\Controller\Noroute\Index',
            array('request' => $this->_requestMock, 'view' => $this->_viewMock)
        );
    }

    public function testIndexActionWhenStatusNotLoaded()
    {
        $this->_requestMock->expects(
            $this->once()
        )->method(
            'getParam'
        )->with(
            '__status__'
        )->will(
            $this->returnValue($this->_statusMock)
        );
        $this->_statusMock->expects($this->any())->method('getLoaded')->will($this->returnValue(false));
        $this->_viewMock->expects($this->once())->method('loadLayout')->with(array('default', 'noroute'));
        $this->_viewMock->expects($this->once())->method('renderLayout');
        $this->_controller->execute();
    }

    public function testIndexActionWhenStatusLoaded()
    {
        $this->_requestMock->expects(
            $this->once()
        )->method(
            'getParam'
        )->with(
            '__status__'
        )->will(
            $this->returnValue($this->_statusMock)
        );
        $this->_statusMock->expects($this->any())->method('getLoaded')->will($this->returnValue(true));
        $this->_statusMock->expects($this->any())->method('getForwarded')->will($this->returnValue(false));
        $this->_viewMock->expects($this->never())->method('loadLayout');
        $this->_requestMock->expects(
            $this->once()
        )->method(
            'setActionName'
        )->will(
            $this->returnValue($this->_requestMock)
        );
        $this->_controller->execute();
    }

    public function testIndexActionWhenStatusNotInstanceofMagentoObject()
    {
        $this->_requestMock->expects(
            $this->once()
        )->method(
            'getParam'
        )->with(
            '__status__'
        )->will(
            $this->returnValue('string')
        );
        $this->_controller->execute();
    }
}
