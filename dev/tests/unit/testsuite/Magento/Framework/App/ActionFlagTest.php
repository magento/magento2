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
namespace Magento\Framework\App;

class ActionFlagTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\ActionFlag
     */
    protected $_actionFlag;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_requestMock;

    protected function setUp()
    {

        $this->_requestMock = $this->getMock('Magento\Framework\App\Request\Http', array(), array(), '', false);
        $this->_actionFlag = new \Magento\Framework\App\ActionFlag($this->_requestMock);
    }

    public function testSetIfActionNotExist()
    {
        $this->_requestMock->expects($this->once())->method('getActionName')->will($this->returnValue('action_name'));
        $this->_requestMock->expects($this->once())->method('getRequestedRouteName');
        $this->_requestMock->expects($this->once())->method('getRequestedControllerName');
        $this->_actionFlag->set('', 'flag', 'value');
    }

    public function testSetIfActionExist()
    {
        $this->_requestMock->expects($this->never())->method('getActionName');
        $this->_requestMock->expects($this->once())->method('getRequestedRouteName');
        $this->_requestMock->expects($this->once())->method('getRequestedControllerName');
        $this->_actionFlag->set('action', 'flag', 'value');
    }

    public function testGetIfFlagNotExist()
    {
        $this->_requestMock->expects($this->once())->method('getActionName')->will($this->returnValue('action_name'));
        $this->_requestMock->expects($this->once())->method('getRequestedRouteName');
        $this->_requestMock->expects($this->once())->method('getRequestedControllerName');
        $this->assertEquals(array(), $this->_actionFlag->get(''));
    }

    public function testGetIfFlagExist()
    {
        $this->_requestMock->expects($this->never())->method('getActionName');
        $this->_requestMock->expects(
            $this->exactly(3)
        )->method(
            'getRequestedRouteName'
        )->will(
            $this->returnValue('route')
        );
        $this->_requestMock->expects(
            $this->exactly(3)
        )->method(
            'getRequestedControllerName'
        )->will(
            $this->returnValue('controller')
        );
        $this->_actionFlag->set('action', 'flag', 'value');
        $this->assertEquals('value', $this->_actionFlag->get('action', 'flag'));
    }

    public function testGetIfFlagWithControllerKryNotExist()
    {
        $this->_requestMock->expects($this->never())->method('getActionName');
        $this->_requestMock->expects(
            $this->once()
        )->method(
            'getRequestedRouteName'
        )->will(
            $this->returnValue('route')
        );
        $this->_requestMock->expects(
            $this->once()
        )->method(
            'getRequestedControllerName'
        )->will(
            $this->returnValue('controller')
        );
        $this->assertEquals(false, $this->_actionFlag->get('action', 'flag'));
    }
}
