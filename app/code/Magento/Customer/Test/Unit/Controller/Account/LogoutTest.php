<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Controller\Account;

use Magento\Customer\Controller\Account\Logout;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Stdlib\Cookie\CookieMetadata;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\PhpCookieManager;

class LogoutTest extends \PHPUnit_Framework_TestCase
{
    /** @var Logout */
    protected $controller;

    /** @var \Magento\Framework\App\Action\Context|\PHPUnit_Framework_MockObject_MockObject */
    protected $contextMock;

    /** @var \Magento\Customer\Model\Session|\PHPUnit_Framework_MockObject_MockObject */
    protected $sessionMock;

    /** @var CookieMetadataFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $cookieMetadataFactory;

    /** @var PhpCookieManager|\PHPUnit_Framework_MockObject_MockObject */
    protected $cookieManager;

    /** @var CookieMetadata|\PHPUnit_Framework_MockObject_MockObject */
    protected $cookieMetadata;

    /** @var Redirect|\PHPUnit_Framework_MockObject_MockObject */
    protected $resultRedirect;

    /** @var RedirectFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $redirectFactory;

    /** @var RedirectInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $redirect;

    protected function setUp()
    {
        $this->contextMock = $this->getMockBuilder(\Magento\Framework\App\Action\Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->sessionMock = $this->getMockBuilder(\Magento\Customer\Model\Session::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId', 'logout', 'setBeforeAuthUrl', 'setLastCustomerId'])
            ->getMock();

        $this->cookieMetadataFactory = $this->getMockBuilder(CookieMetadataFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->cookieManager = $this->getMockBuilder(PhpCookieManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->cookieMetadata = $this->getMockBuilder(CookieMetadata::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->redirectFactory = $this->getMockBuilder(RedirectFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultRedirect = $this->getMockBuilder(Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->contextMock->expects($this->once())
            ->method('getResultRedirectFactory')
            ->willReturn($this->redirectFactory);

        $this->redirect = $this->getMockBuilder(RedirectInterface::class)
            ->getMockForAbstractClass();
        $this->contextMock->expects($this->once())
            ->method('getRedirect')
            ->willReturn($this->redirect);

        $this->controller = new Logout($this->contextMock, $this->sessionMock);

        $refClass = new \ReflectionClass(Logout::class);
        $cookieMetadataManagerProperty = $refClass->getProperty('cookieMetadataManager');
        $cookieMetadataManagerProperty->setAccessible(true);
        $cookieMetadataManagerProperty->setValue($this->controller, $this->cookieManager);

        $cookieMetadataFactoryProperty = $refClass->getProperty('cookieMetadataFactory');
        $cookieMetadataFactoryProperty->setAccessible(true);
        $cookieMetadataFactoryProperty->setValue($this->controller, $this->cookieMetadataFactory);
    }

    public function testExecute()
    {
        $customerId = 1;
        $refererUrl = 'http://referer.url';

        $this->sessionMock->expects($this->once())
            ->method('getId')
            ->willReturn($customerId);
        $this->sessionMock->expects($this->once())
            ->method('logout')
            ->willReturnSelf();
        $this->redirect->expects($this->once())
            ->method('getRefererUrl')
            ->willReturn($refererUrl);
        $this->sessionMock->expects($this->once())
            ->method('setBeforeAuthUrl')
            ->with($refererUrl)
            ->willReturnSelf();
        $this->sessionMock->expects($this->once())
            ->method('setLastCustomerId')
            ->with($customerId);

        $this->cookieManager->expects($this->once())
            ->method('getCookie')
            ->with('mage-cache-sessid')
            ->willReturn(true);
        $this->cookieMetadataFactory->expects($this->once())
            ->method('createCookieMetadata')
            ->willReturn($this->cookieMetadata);
        $this->cookieMetadata->expects($this->once())
            ->method('setPath')
            ->with('/');
        $this->cookieManager->expects($this->once())
            ->method('deleteCookie')
            ->with('mage-cache-sessid', $this->cookieMetadata);
        $this->redirectFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->resultRedirect);
        $this->resultRedirect->expects($this->once())
            ->method('setPath')
            ->with('*/*/logoutSuccess');
        $this->assertSame($this->resultRedirect, $this->controller->execute());
    }
}
