<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Security\Test\Unit\Model\Plugin;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Security\Model\SecurityCookie;

/**
 * Test class for \Magento\Security\Model\Plugin\LoginController testing
 */
class LoginControllerTest extends \PHPUnit\Framework\TestCase
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

        $this->messageManagerMock = $this->createMock(\Magento\Framework\Message\ManagerInterface::class);

        $this->adminSessionsManagerMock = $this->createPartialMock(
            \Magento\Security\Model\AdminSessionsManager::class,
            ['getLogoutReasonMessageByStatus']
        );

        $this->securityCookieMock = $this->createPartialMock(
            SecurityCookie::class,
            ['getLogoutReasonCookie', 'deleteLogoutReasonCookie']
        );

        $this->backendControllerAuthLoginMock = $this->createPartialMock(
            \Magento\Backend\Controller\Adminhtml\Auth\Login::class,
            ['getRequest', 'getUrl']
        );

        $this->requestMock = $this->createPartialMock(\Magento\Framework\App\Request\Http::class, ['getUri']);

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
            ->method('addErrorMessage')
            ->with($errorMessage);

        $this->securityCookieMock->expects($this->once())
            ->method('deleteLogoutReasonCookie')
            ->willReturnSelf();

        $this->controller->beforeExecute($this->backendControllerAuthLoginMock);
    }
}
