<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Controller\Account;

use Magento\Customer\Controller\Account\Logout;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Stdlib\Cookie\CookieMetadata;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\PhpCookieManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;

class LogoutTest extends TestCase
{
    use MockCreationTrait;

    /** @var Logout */
    protected $controller;

    /** @var Context|MockObject */
    protected $contextMock;

    /** @var Session|MockObject */
    protected $sessionMock;

    /** @var CookieMetadataFactory|MockObject */
    protected $cookieMetadataFactory;

    /** @var PhpCookieManager|MockObject */
    protected $cookieManager;

    /** @var CookieMetadata|MockObject */
    protected $cookieMetadata;

    /** @var Redirect|MockObject */
    protected $resultRedirect;

    /** @var RedirectFactory|MockObject */
    protected $redirectFactory;

    /** @var RedirectInterface|MockObject */
    protected $redirect;

    protected function setUp(): void
    {
        $this->contextMock = $this->createMock(Context::class);
        
        $this->sessionMock = $this->createPartialMockWithReflection(
            Session::class,
            ['getId', 'logout', 'setBeforeAuthUrl', 'setLastCustomerId']
        );

        $this->cookieMetadataFactory = $this->createMock(CookieMetadataFactory::class);
        $this->cookieManager = $this->createMock(PhpCookieManager::class);
        $this->cookieMetadata = $this->createMock(CookieMetadata::class);
        $this->redirectFactory = $this->createMock(RedirectFactory::class);
        $this->resultRedirect = $this->createMock(Redirect::class);
        
        $this->contextMock->expects($this->once())
            ->method('getResultRedirectFactory')
            ->willReturn($this->redirectFactory);

        $this->redirect = $this->createMock(RedirectInterface::class);
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
