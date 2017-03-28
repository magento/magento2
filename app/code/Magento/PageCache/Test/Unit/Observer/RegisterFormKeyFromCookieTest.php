<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\PageCache\Test\Unit\Observer;

use Magento\Framework\App\PageCache\FormKey;
use Magento\Framework\Escaper;
use Magento\Framework\Session\Config\ConfigInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\PageCache\Observer\RegisterFormKeyFromCookie;

class RegisterFormKeyFromCookieTest extends \PHPUnit_Framework_TestCase
{
    /** @var RegisterFormKeyFromCookie */
    protected $observer;

    /** @var \PHPUnit_Framework_MockObject_MockObject|FormKey */
    protected $cookieFormKey;

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Data\Form\FormKey */
    protected $sessionFormKey;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|CookieMetadataFactory
     */
    protected $cookieMetadataFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ConfigInterface
     */
    protected $sessionConfig;

    /** @var \PHPUnit_Framework_MockObject_MockObject|Escaper */
    protected $escaper;

    /**
     * @var \Magento\Framework\Event\Observer|\PHPUnit_Framework_MockObject_MockObject|
     */
    protected $observerMock;

    /**
     * Set up all mocks and data for test
     */
    protected function setUp()
    {
        $this->cookieFormKey = $this->getMockBuilder(
            \Magento\Framework\App\PageCache\FormKey::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->escaper = $this->getMockBuilder(
            \Magento\Framework\Escaper::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->sessionFormKey = $this->getMockBuilder(
            \Magento\Framework\Data\Form\FormKey::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->cookieMetadataFactory = $this->getMockBuilder(
            \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->sessionConfig = $this->getMock(
            \Magento\Framework\Session\Config\ConfigInterface::class
        );

        $this->observerMock = $this->getMock(\Magento\Framework\Event\Observer::class);

        $this->observer = new RegisterFormKeyFromCookie(
            $this->cookieFormKey,
            $this->escaper,
            $this->sessionFormKey,
            $this->cookieMetadataFactory,
            $this->sessionConfig
        );
    }

    public function testExecuteNoCookie()
    {
        $this->cookieFormKey->expects(static::once())
            ->method('get')
            ->willReturn(null);
        $this->cookieFormKey->expects(static::never())
            ->method('set');
        $this->sessionFormKey->expects(static::never())
            ->method('set');

        $this->observer->execute($this->observerMock);
    }

    public function testExecute()
    {
        $formKey = 'form_key';
        $escapedFormKey = 'escaped_form_key';
        $cookieDomain = 'example.com';
        $cookiePath = '/';
        $cookieLifetime = 3600;

        $cookieMetadata = $this->getMockBuilder(
            \Magento\Framework\Stdlib\Cookie\PublicCookieMetadata::class
        )
            ->disableOriginalConstructor()
            ->getMock();

        $this->cookieFormKey->expects(static::any())
            ->method('get')
            ->willReturn($formKey);
        $this->cookieMetadataFactory->expects(static::once())
            ->method('createPublicCookieMetadata')
            ->willReturn(
                $cookieMetadata
            );

        $this->sessionConfig->expects(static::once())
            ->method('getCookieDomain')
            ->willReturn(
                $cookieDomain
            );
        $cookieMetadata->expects(static::once())
            ->method('setDomain')
            ->with(
                $cookieDomain
            );
        $this->sessionConfig->expects(static::once())
            ->method('getCookiePath')
            ->willReturn(
                $cookiePath
            );
        $cookieMetadata->expects(static::once())
            ->method('setPath')
            ->with(
                $cookiePath
            );
        $this->sessionConfig->expects(static::once())
            ->method('getCookieLifetime')
            ->willReturn(
                $cookieLifetime
            );
        $cookieMetadata->expects(static::once())
            ->method('setDuration')
            ->with(
                $cookieLifetime
            );

        $this->cookieFormKey->expects(static::once())
            ->method('set')
            ->with(
                $formKey,
                $cookieMetadata
            );

        $this->escaper->expects(static::once())
            ->method('escapeHtml')
            ->with($formKey)
            ->willReturn($escapedFormKey);

        $this->sessionFormKey->expects(static::once())
            ->method('set')
            ->with($escapedFormKey);

        $this->observer->execute($this->observerMock);
    }
}
