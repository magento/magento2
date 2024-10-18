<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Backend\Test\Unit\Model;

use Magento\Backend\Model\Auth;
use Magento\Backend\Model\Auth\Credential\StorageInterface;
use Magento\Framework\Data\Collection\ModelFactory;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AuthTest extends TestCase
{
    /**
     * @var Auth
     */
    protected $_model;

    /**
     * @var MockObject
     */
    protected $_eventManagerMock;

    /**
     * @var MockObject
     */
    protected $_credentialStorage;

    /**
     * @var MockObject
     */
    protected $_modelFactoryMock;

    protected function setUp(): void
    {
        $this->_eventManagerMock = $this->getMockForAbstractClass(ManagerInterface::class);
        $this->_credentialStorage = $this->getMockBuilder(
            StorageInterface::class
        )
            ->addMethods(['getId'])
            ->getMockForAbstractClass();
        $this->_modelFactoryMock = $this->createMock(ModelFactory::class);
        $objectManager = new ObjectManager($this);
        $this->_model = $objectManager->getObject(
            Auth::class,
            [
                'eventManager' => $this->_eventManagerMock,
                'credentialStorage' => $this->_credentialStorage,
                'modelFactory' => $this->_modelFactoryMock
            ]
        );
    }

    public function testLoginFailed()
    {
        $this->expectException('Magento\Framework\Exception\AuthenticationException');
        $this->_modelFactoryMock
            ->expects($this->once())
            ->method('create')
            ->with(StorageInterface::class)
            ->willReturn($this->_credentialStorage);
        $exceptionMock = new LocalizedException(
            __(
                'The account sign-in was incorrect or your account is disabled temporarily. '
                . 'Please wait and try again later.'
            )
        );
        $this->_credentialStorage
            ->expects($this->once())
            ->method('login')
            ->with('username', 'password')
            ->willThrowException($exceptionMock);
        $this->_credentialStorage->expects($this->never())->method('getId');
        $this->_eventManagerMock->expects($this->once())->method('dispatch')->with('backend_auth_user_login_failed');
        $this->_model->login('username', 'password');

        $this->expectExceptionMessage(
            'The account sign-in was incorrect or your account is disabled temporarily. '
            . 'Please wait and try again later.'
        );
    }
}
