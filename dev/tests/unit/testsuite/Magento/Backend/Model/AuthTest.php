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
namespace Magento\Backend\Model;

class AuthTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Backend\Model\Auth
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_eventManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_credentialStorage;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_modelFactoryMock;

    protected function setUp()
    {
        $this->_eventManagerMock = $this->getMock('\Magento\Event\ManagerInterface');
        $this->_credentialStorage = $this->getMock('\Magento\Backend\Model\Auth\Credential\StorageInterface');
        $this->_modelFactoryMock = $this->getMock('\Magento\Core\Model\Factory', array(), array(), '', false);
        $this->_model = new \Magento\Backend\Model\Auth(
            $this->_eventManagerMock,
            $this->getMock('\Magento\Backend\Helper\Data', array(), array(), '', false),
            $this->getMock('\Magento\Backend\Model\Auth\StorageInterface'),
            $this->_credentialStorage,
            $this->getMock('\Magento\App\ConfigInterface', array(), array(), '', false),
            $this->_modelFactoryMock
        );
    }

    /**
     * @expectedException \Magento\Backend\Model\Auth\Exception
     * @expectedExceptionMessage Please correct the user name or password.
     */
    public function testLoginFailed()
    {
        $this->_modelFactoryMock
            ->expects($this->once())
            ->method('create')
            ->with('Magento\Backend\Model\Auth\Credential\StorageInterface')
            ->will($this->returnValue($this->_credentialStorage));
        $exceptionMock = new \Magento\Model\Exception;
        $this->_credentialStorage
            ->expects($this->once())
            ->method('login')
            ->with('username', 'password')
            ->will($this->throwException($exceptionMock));
        $this->_credentialStorage->expects($this->never())->method('getId');
        $this->_eventManagerMock->expects($this->once())->method('dispatch')->with('backend_auth_user_login_failed');
        $this->_model->login('username', 'password');
    }
}
