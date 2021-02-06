<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for Auth
 */
class AuthTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Backend\Model\Auth
     */
    protected $_model;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_eventManagerMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_credentialStorage;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_modelFactoryMock;

    protected function setUp(): void
    {
        $this->_eventManagerMock = $this->createMock(\Magento\Framework\Event\ManagerInterface::class);
        $this->_credentialStorage = $this->getMockBuilder(
            \Magento\Backend\Model\Auth\Credential\StorageInterface::class
        )
            ->setMethods(['getId'])
            ->getMockForAbstractClass();
        $this->_modelFactoryMock = $this->createMock(\Magento\Framework\Data\Collection\ModelFactory::class);
        $objectManager = new ObjectManager($this);
        $this->_model = $objectManager->getObject(
            \Magento\Backend\Model\Auth::class,
            [
                'eventManager' => $this->_eventManagerMock,
                'credentialStorage' => $this->_credentialStorage,
                'modelFactory' => $this->_modelFactoryMock
            ]
        );
    }

    /**
     */
    public function testLoginFailed()
    {
        $this->expectException(\Magento\Framework\Exception\AuthenticationException::class);

        $this->_modelFactoryMock
            ->expects($this->once())
            ->method('create')
            ->with(\Magento\Backend\Model\Auth\Credential\StorageInterface::class)
            ->willReturn($this->_credentialStorage);
        $exceptionMock = new \Magento\Framework\Exception\LocalizedException(
            __(
                'The account sign-in was incorrect or your account is disabled temporarily. '
                . 'Please wait and try again later.'
            )
        );
        $this->_credentialStorage
            ->expects($this->once())
            ->method('login')
            ->with('username', 'password')
            ->will($this->throwException($exceptionMock));
        $this->_credentialStorage->expects($this->never())->method('getId');
        $this->_eventManagerMock->expects($this->once())->method('dispatch')->with('backend_auth_user_login_failed');
        $this->_model->login('username', 'password');

        $this->expectExceptionMessage(
            'The account sign-in was incorrect or your account is disabled temporarily. '
            . 'Please wait and try again later.'
        );
    }
}
