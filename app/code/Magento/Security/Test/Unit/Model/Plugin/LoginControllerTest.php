<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Security\Test\Unit\Model\Plugin;

use Magento\Backend\Controller\Adminhtml\Auth\Login;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Security\Model\AdminSessionsManager;
use Magento\Security\Model\Plugin\LoginController;
use Magento\Security\Model\SecurityCookie;
use PHPUnit\Framework\TestCase;

/**
 * Test class for \Magento\Security\Model\Plugin\LoginController testing
 */
class LoginControllerTest extends TestCase
{
    /** @var  LoginController */
    protected $controller;

    /** @var ManagerInterface */
    protected $messageManagerMock;

    /** @var AdminSessionsManager */
    protected $adminSessionsManagerMock;

    /** @var SecurityCookie */
    protected $securityCookieMock;

    /** @var Login */
    protected $backendControllerAuthLoginMock;

    /** @var Http */
    protected $requestMock;

    /** @var  ObjectManager */
    protected $objectManager;

    /**
     * Init mocks for tests
     * @return void
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->messageManagerMock = $this->getMockForAbstractClass(ManagerInterface::class);

        $this->adminSessionsManagerMock = $this->createPartialMock(
            AdminSessionsManager::class,
            ['getLogoutReasonMessageByStatus']
        );

        $this->securityCookieMock = $this->createPartialMock(
            SecurityCookie::class,
            ['getLogoutReasonCookie', 'deleteLogoutReasonCookie']
        );

        $this->backendControllerAuthLoginMock = $this->createPartialMock(
            Login::class,
            ['getRequest', 'getUrl']
        );

        $this->requestMock = $this->createPartialMock(Http::class, ['getUri']);

        $this->controller = $this->objectManager->getObject(
            LoginController::class,
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
            ->method('addErrorMessage')
            ->with($errorMessage);

        $this->securityCookieMock->expects($this->once())
            ->method('deleteLogoutReasonCookie')
            ->willReturnSelf();

        $this->controller->beforeExecute($this->backendControllerAuthLoginMock);
    }
}
