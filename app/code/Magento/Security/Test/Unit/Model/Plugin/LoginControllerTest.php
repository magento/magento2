<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Security\Test\Unit\Model\Plugin;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

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

    /** @var \Magento\Framework\Stdlib\Cookie\PhpCookieManager */
    protected $phpCookieManagerMock;

    /** @var \Magento\Framework\Stdlib\Cookie\CookieReaderInterface */
    protected $cookieReaderMock;

    /** @var \Magento\Framework\Stdlib\Cookie\PublicCookieMetadata */
    protected $backendDataMock;

    /** @var \Magento\Framework\Stdlib\Cookie\PublicCookieMetadataFactory */
    protected $cookieMetadataFactoryMock;

    /** @var \Magento\Framework\Stdlib\Cookie\PublicCookieMetadata */
    protected $cookieMetadataMock;

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
            '\Magento\Framework\Message\ManagerInterface',
            [],
            [],
            '',
            false
        );

        $this->adminSessionsManagerMock = $this->getMock(
            '\Magento\Security\Model\AdminSessionsManager',
            ['getLogoutReasonMessageByStatus'],
            [],
            '',
            false
        );

        $this->phpCookieManagerMock = $this->getMock(
            '\Magento\Framework\Stdlib\Cookie\PhpCookieManager',
            ['setPublicCookie'],
            [],
            '',
            false
        );

        $this->cookieReaderMock = $this->getMock(
            '\Magento\Framework\Stdlib\Cookie\CookieReaderInterface',
            [],
            [],
            '',
            false
        );

        $this->backendDataMock = $this->getMock(
            '\Magento\Backend\Helper\Data',
            [],
            [],
            '',
            false
        );

        $this->cookieMetadataFactoryMock = $this->getMock(
            '\Magento\Framework\Stdlib\Cookie\PublicCookieMetadataFactory',
            ['create'],
            [],
            '',
            false
        );

        $this->cookieMetadataMock = $this->getMock(
            '\Magento\Framework\Stdlib\Cookie\PublicCookieMetadata',
            ['setPath', 'setDuration'],
            [],
            '',
            false
        );

        $this->backendControllerAuthLoginMock = $this->getMock(
            '\Magento\Backend\Controller\Adminhtml\Auth\Login',
            ['getRequest', 'getUrl'],
            [],
            '',
            false
        );

        $this->requestMock = $this->getMock(
            '\Magento\Framework\App\Request\Http',
            ['getUri'],
            [],
            '',
            false
        );

        $this->controller = $this->objectManager->getObject(
            '\Magento\Security\Model\Plugin\LoginController',
            [
                'messageManager' => $this->messageManagerMock,
                'sessionsManager' => $this->adminSessionsManagerMock,
                'phpCookieManager' => $this->phpCookieManagerMock,
                'cookieReader' => $this->cookieReaderMock,
                'backendData' => $this->backendDataMock,
                'cookieMetadataFactory' => $this->cookieMetadataFactoryMock
            ]
        );
    }

    /**
     * @return void
     */
    public function testBeforeExecute()
    {
        $cookie = '123';
        $uri = '/uri/';
        $errorMessage = 'Error Message';
        $frontName = 'FrontName';

        $this->cookieReaderMock->expects($this->once())
            ->method('getCookie')
            ->with(
                \Magento\Security\Model\Plugin\AuthSession::LOGOUT_REASON_CODE_COOKIE_NAME,
                -1
            )
            ->willReturn($cookie);

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
            ->willReturn($errorMessage);

        $this->messageManagerMock->expects($this->once())
            ->method('addError')
            ->with($errorMessage);

        $this->cookieMetadataFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->cookieMetadataMock);

        $this->backendDataMock->expects($this->once())
            ->method('getAreaFrontName')
            ->willReturn($frontName);

        $this->cookieMetadataMock->expects($this->once())
            ->method('setPath')
            ->with('/' . $frontName)
            ->willReturnSelf();

        $this->cookieMetadataMock->expects($this->once())
            ->method('setDuration')
            ->with(-1)
            ->willReturnSelf();

        $this->phpCookieManagerMock->expects($this->once())
            ->method('setPublicCookie')
            ->with(
                \Magento\Security\Model\Plugin\AuthSession::LOGOUT_REASON_CODE_COOKIE_NAME,
                '',
                $this->cookieMetadataMock
            )
            ->willReturnSelf();

        $this->controller->beforeExecute($this->backendControllerAuthLoginMock);
    }

}
