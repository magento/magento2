<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Security\Test\Unit\Model\Plugin;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Security\Model\SecurityCookie;

/**
 * Test class for \Magento\Security\Model\Plugin\LoginController testing
 */
class LoginControllerTest extends \PHPUnit_Framework_TestCase
{
    /** @var  \Magento\Security\Model\Plugin\LoginController */
    protected $controller;

    /** @var \Magento\Framework\Message\ManagerInterface */
    protected $messageManagerMock;

    /** @var \Magento\Security\Model\AdminSessionsManager */
    protected $adminSessionsManagerMock;

    /** @var SecurityCookie */
    protected $securityCookieMock;

    /** @var \Magento\Backend\Controller\Adminhtml\Auth\Login */
    protected $backendControllerAuthLoginMock;

    /** @var \Magento\Framework\App\Request\Http */
    protected $requestMock;

    /** @var  \Magento\Framework\TestFramework\Unit\Helper\ObjectManager */
    protected $objectManager;

    /**
     * Init mocks for tests
     * @return void
     */
    public function setUp()
    {
        $this->objectManager = new ObjectManager($this);

        $this->messageManagerMock = $this->getMock(
            \Magento\Framework\Message\ManagerInterface::class,
            [],
            [],
            '',
            false
        );

        $this->adminSessionsManagerMock = $this->getMock(
            \Magento\Security\Model\AdminSessionsManager::class,
            ['getLogoutReasonMessageByStatus'],
            [],
            '',
            false
        );

        $this->securityCookieMock = $this->getMock(
            SecurityCookie::class,
            ['getLogoutReasonCookie', 'deleteLogoutReasonCookie'],
            [],
            '',
            false
        );

        $this->backendControllerAuthLoginMock = $this->getMock(
            \Magento\Backend\Controller\Adminhtml\Auth\Login::class,
            ['getRequest', 'getUrl'],
            [],
            '',
            false
        );

        $this->requestMock = $this->getMock(
            \Magento\Framework\App\Request\Http::class,
            ['getUri'],
            [],
            '',
            false
        );

        $this->controller = $this->objectManager->getObject(
            \Magento\Security\Model\Plugin\LoginController::class,
            [
                'messageManager' => $this->messageManagerMock,
                'sessionsManager' => $this->adminSessionsManagerMock,
                'securityCookie' => $this->securityCookieMock
            ]
        );
    }

    /**
     * @return void
     */
    public function testBeforeExecute()
    {
        $logoutReasonCode = 2;
        $uri = '/uri/';
        $errorMessage = 'Error Message';

        $this->securityCookieMock->expects($this->once())
            ->method('getLogoutReasonCookie')
            ->willReturn($logoutReasonCode);

        $this->backendControllerAuthLoginMock->expects($this->once())
            ->method('getRequest')
            ->willReturn($this->requestMock);

        $this->requestMock->expects($this->once())
            ->method('getUri')
            ->willReturn($uri);

        $this->backendControllerAuthLoginMock->expects($this->once())
            ->method('getUrl')
            ->willReturn($uri);

        $this->adminSessionsManagerMock->expects($this->once())
            ->method('getLogoutReasonMessageByStatus')
            ->with($logoutReasonCode)
            ->willReturn($errorMessage);

        $this->messageManagerMock->expects($this->once())
            ->method('addError')
            ->with($errorMessage);

        $this->securityCookieMock->expects($this->once())
            ->method('deleteLogoutReasonCookie')
            ->willReturnSelf();

        $this->controller->beforeExecute($this->backendControllerAuthLoginMock);
    }
}
