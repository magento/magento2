<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Security\Test\Unit\Model\Plugin;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test class for \Magento\Security\Model\Plugin\AuthSession testing
 */
class AuthSessionTest extends \PHPUnit_Framework_TestCase
{
    /** @var  \Magento\Security\Model\Plugin\AuthSession */
    protected $model;

    /** @var \Magento\Security\Model\AdminSessionsManager */
    protected $adminSessionsManagerMock;

    /** @var \Magento\Framework\App\RequestInterface */
    protected $requestMock;

    /** @var \Magento\Backend\Model\Auth\Session */
    protected $authSessionMock;

    /** @var \Magento\Security\Model\AdminSessionInfo */
    protected $currentSessionMock;

    /** @var \Magento\Framework\Message\ManagerInterface */
    protected $messageManagerMock;

    /** @var \Magento\Framework\Stdlib\Cookie\PublicCookieMetadataFactory */
    protected $cookieMetadataFactoryMock;

    /** @var \Magento\Framework\Stdlib\Cookie\PublicCookieMetadata */
    protected $cookieMetadataMock;

    /** @var \Magento\Framework\Stdlib\Cookie\PublicCookieMetadata */
    protected $backendDataMock;

    /** @var \Magento\Framework\Stdlib\Cookie\PhpCookieManager */
    protected $phpCookieManagerMock;

    /** @var  \Magento\Framework\TestFramework\Unit\Helper\ObjectManager */
    protected $objectManager;

    /**
     * Init mocks for tests
     * @return void
     */
    public function setUp()
    {
        $this->objectManager = new ObjectManager($this);

        $this->adminSessionsManagerMock = $this->getMock(
            '\Magento\Security\Model\AdminSessionsManager',
            ['getCurrentSession', 'processProlong', 'getLogoutReasonMessage'],
            [],
            '',
            false
        );

        $this->requestMock = $this->getMockForAbstractClass(
            '\Magento\Framework\App\RequestInterface',
            ['getParam', 'getModuleName', 'getActionName'],
            '',
            false
        );

        $this->authSessionMock = $this->getMock(
            '\Magento\Backend\Model\Auth\Session',
            ['destroy'],
            [],
            '',
            false
        );

        $this->currentSessionMock = $this->getMock(
            '\Magento\Security\Model\AdminSessionInfo',
            ['isActive', 'getStatus'],
            [],
            '',
            false
        );

        $this->messageManagerMock = $this->getMock(
            '\Magento\Framework\Message\ManagerInterface',
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
            ['setPath'],
            [],
            '',
            false
        );

        $this->backendDataMock = $this->getMock(
            '\Magento\Backend\Helper\Data',
            ['getAreaFrontName'],
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

        $this->model = $this->objectManager->getObject(
            '\Magento\Security\Model\Plugin\AuthSession',
            [
                'sessionsManager' => $this->adminSessionsManagerMock,
                'request' => $this->requestMock,
                'messageManager' => $this->messageManagerMock,
                'cookieMetadataFactory' => $this->cookieMetadataFactoryMock,
                'backendData' => $this->backendDataMock,
                'phpCookieManager' => $this->phpCookieManagerMock
            ]
        );

        $this->requestMock->expects($this->any())
            ->method('getModuleName')
            ->willReturn('notSecurity');

        $this->requestMock->expects($this->any())
            ->method('getActionName')
            ->willReturn('notCheck');

        $this->adminSessionsManagerMock->expects($this->any())
            ->method('getCurrentSession')
            ->willReturn($this->currentSessionMock);
    }

    /**
     * @return void
     */
    public function testAroundProlongSessionIsNotActiveAndIsNotAjaxRequest()
    {
        $result = 'result';
        $errorMessage = 'Error Message';

        $proceed = function () use ($result) {
            return $result;
        };

        $this->currentSessionMock->expects($this->any())
            ->method('isActive')
            ->willReturn(false);

        $this->authSessionMock->expects($this->once())
            ->method('destroy');

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('isAjax')
            ->willReturn(false);

        $this->adminSessionsManagerMock->expects($this->once())
            ->method('getLogoutReasonMessage')
            ->willReturn($errorMessage);

        $this->messageManagerMock->expects($this->once())
            ->method('addError')
            ->with($errorMessage);

        $this->model->aroundProlong($this->authSessionMock, $proceed);
    }

    /**
     * @return void
     */
    public function testAroundProlongSessionIsNotActiveAndIsAjaxRequest()
    {
        $result = 'result';
        $frontName = 'FrontName';
        $status = 1;

        $proceed = function () use ($result) {
            return $result;
        };

        $this->currentSessionMock->expects($this->any())
            ->method('isActive')
            ->willReturn(false);

        $this->authSessionMock->expects($this->once())
            ->method('destroy');

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('isAjax')
            ->willReturn(true);

        $this->currentSessionMock->expects($this->once())
            ->method('getStatus')
            ->willReturn($status);

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

        $this->phpCookieManagerMock->expects($this->once())
            ->method('setPublicCookie')
            ->with(
                \Magento\Security\Model\Plugin\AuthSession::LOGOUT_REASON_CODE_COOKIE_NAME,
                $status,
                $this->cookieMetadataMock
            )
            ->willReturnSelf();

        $this->model->aroundProlong($this->authSessionMock, $proceed);
    }

    /**
     * @return void
     */
    public function testAroundProlongSessionIsActive()
    {
        $result = 'result';
        $proceed = function () use ($result) {
            return $result;
        };

        $this->currentSessionMock->expects($this->any())
            ->method('isActive')
            ->willReturn(true);

        $this->adminSessionsManagerMock->expects($this->any())
            ->method('processProlong');

        $this->assertEquals($result, $this->model->aroundProlong($this->authSessionMock, $proceed));
    }
}
