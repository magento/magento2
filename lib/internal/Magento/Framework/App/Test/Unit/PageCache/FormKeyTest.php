<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\Test\Unit\PageCache;

use Magento\Framework\App\PageCache\FormKey;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\PublicCookieMetadata;

/**
 * Class FormKeyTest
 */
class FormKeyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Version instance
     *
     * @var FormKey
     */
    protected $formKey;

    /**
     * Cookie mock
     *
     * @var CookieManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $cookieManagerMock;

    /**
     * @var CookieMetadataFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cookieMetadataFactory;

    /**
     * @var SessionManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sessionManager;

    /**
     * Create cookie mock and FormKey instance
     */
    protected function setUp()
    {
        $this->cookieManagerMock = $this->getMock(\Magento\Framework\Stdlib\CookieManagerInterface::class);
        $this->cookieMetadataFactory = $this->getMockBuilder(
            \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->sessionManager = $this->getMock(\Magento\Framework\Session\SessionManagerInterface::class);
        $this->formKey = new FormKey(
            $this->cookieManagerMock,
            $this->cookieMetadataFactory,
            $this->sessionManager
        );
    }

    public function testGet()
    {
        //Data
        $formKey = 'test_from_key';

        //Verification
        $this->cookieManagerMock->expects($this->once())
            ->method('getCookie')
            ->with(FormKey::COOKIE_NAME)
            ->will($this->returnValue($formKey));

        $this->assertEquals($formKey, $this->formKey->get());
    }

    public function testSet()
    {
        $formKeyValue = 'form_key';
        /** @var PublicCookieMetadata|\PHPUnit_Framework_MockObject_MockObject $metadata */
        $metadata = $this->getMockBuilder(
            \Magento\Framework\Stdlib\Cookie\PublicCookieMetadata::class
        )
            ->disableOriginalConstructor()
            ->getMock();

        $this->cookieManagerMock->expects(static::once())
            ->method('setPublicCookie')
            ->with(
                FormKey::COOKIE_NAME,
                $formKeyValue,
                $metadata
            );

        $this->formKey->set($formKeyValue, $metadata);
    }

    public function testDelete()
    {
        $cookiePath = '/';
        $cookieDomain = 'example.com';
        /** @var PublicCookieMetadata|\PHPUnit_Framework_MockObject_MockObject $metadata */
        $metadata = $this->getMockBuilder(
            \Magento\Framework\Stdlib\Cookie\PublicCookieMetadata::class
        )
            ->disableOriginalConstructor()
            ->getMock();

        $this->cookieMetadataFactory->expects(static::once())
            ->method('createCookieMetadata')
            ->willReturn($metadata);

        $this->sessionManager->expects(static::once())
            ->method('getCookiePath')
            ->willReturn($cookiePath);
        $metadata->expects(static::once())
            ->method('setPath')
            ->with($cookiePath)
            ->willReturnSelf();
        $this->sessionManager->expects(static::once())
            ->method('getCookieDomain')
            ->willReturn($cookieDomain);
        $metadata->expects(static::once())
            ->method('setDomain')
            ->with($cookieDomain)
            ->willReturnSelf();

        $this->cookieManagerMock->expects(static::once())
            ->method('deleteCookie')
            ->with(
                FormKey::COOKIE_NAME,
                $metadata
            );

        $this->formKey->delete();
    }
}
