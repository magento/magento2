<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Persistent\Test\Unit\Observer;

use Magento\Persistent\Observer\RefreshCustomerData;

class RefreshCustomerDataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RefreshCustomerData
     */
    private $observer;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\PhpCookieManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cookieManager;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $metadataFactory;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadata|\PHPUnit_Framework_MockObject_MockObject
     */
    private $metadata;

    /**
     * @var \Magento\Customer\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sessionManager;

    public function setUp()
    {
        $this->cookieManager = $this->getMockBuilder(\Magento\Framework\Stdlib\Cookie\PhpCookieManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->metadataFactory = $this->getMockBuilder(\Magento\Framework\Stdlib\Cookie\CookieMetadataFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->metadata = $this->getMockBuilder(\Magento\Framework\Stdlib\Cookie\CookieMetadata::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->sessionManager = $this->getMockBuilder(\Magento\Framework\Session\SessionManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->observer = new RefreshCustomerData($this->cookieManager, $this->metadataFactory);
    }

    /**
     * @param bool $result
     * @param string $callCount
     * @return void
     * @dataProvider beforeStartDataProvider
     */
    public function testBeforeStart($result, $callCount)
    {
        $observerMock = $this->getMock(\Magento\Framework\Event\Observer::class, [], [], '', false);
        $frontendSessionCookieName = 'mage-cache-sessid';
        $this->cookieManager->expects($this->once())
            ->method('getCookie')
            ->with($frontendSessionCookieName)
            ->willReturn($result);

        $this->metadataFactory->expects($this->{$callCount}())
            ->method('createCookieMetadata')
            ->willReturn($this->metadata);
        $this->metadata->expects($this->{$callCount}())
            ->method('setPath')
            ->with('/');
        $this->cookieManager->expects($this->{$callCount}())
            ->method('deleteCookie')
            ->with('mage-cache-sessid', $this->metadata);

        $this->observer->execute($observerMock);
    }

    public function beforeStartDataProvider()
    {
        return [
            [true, 'once'],
            [false, 'never']
        ];
    }
}
