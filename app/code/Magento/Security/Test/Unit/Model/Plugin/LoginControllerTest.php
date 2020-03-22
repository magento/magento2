<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Security\Test\Unit\Model\Plugin;

use Magento\Backend\Controller\Adminhtml\Auth\Login as AdminhtmlLogin;
use Magento\Framework\App\Request\Http as RequestHttp;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Security\Model\AdminSessionsManager;
use Magento\Security\Model\SecurityCookie;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test class for \Magento\Security\Plugin\BackendLoginControllerPlugin testing
 */
class LoginControllerTest extends TestCase
{
    /** @var  \Magento\Security\Plugin\BackendLoginControllerPlugin */
    protected $controller;

    /** @var MockObject|MessageManagerInterface */
    protected $messageManagerMock;

    /** @var MockObject|AdminSessionsManager */
    protected $adminSessionsManagerMock;

    /** @var MockObject|SecurityCookie */
    protected $securityCookieMock;

    /** @var MockObject|AdminhtmlLogin */
    protected $backendControllerAuthLoginMock;

    /** @var MockObject|RequestHttp */
    protected $requestMock;

    /** @var  ObjectManager */
    protected $objectManager;

    /**
     * Init mocks for tests
     * @return void
     */
    public function setUp()
    {
        $this->objectManager = new ObjectManager($this);

        $this->messageManagerMock = $this->createMock(MessageManagerInterface::class);

        $this->adminSessionsManagerMock = $this->createPartialMock(
            AdminSessionsManager::class,
            ['getLogoutReasonMessageByStatus']
        );

        $this->securityCookieMock = $this->createPartialMock(
            SecurityCookie::class,
            ['getLogoutReasonCookie', 'deleteLogoutReasonCookie']
        );

        $this->backendControllerAuthLoginMock = $this->createPartialMock(
            AdminhtmlLogin::class,
            ['getRequest', 'getUrl']
        );

        $this->requestMock = $this->createPartialMock(RequestHttp::class, ['getUri']);

        $this->controller = $this->objectManager->getObject(
            \Magento\Security\Plugin\BackendLoginControllerPlugin::class,
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
