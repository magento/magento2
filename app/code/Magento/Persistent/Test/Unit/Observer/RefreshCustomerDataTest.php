<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Persistent\Test\Unit\Observer;

use Magento\Persistent\Observer\RefreshCustomerData;

class RefreshCustomerDataTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var RefreshCustomerData
     */
    private $observer;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\PhpCookieManager|\PHPUnit\Framework\MockObject\MockObject
     */
    private $cookieManager;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $metadataFactory;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadata|\PHPUnit\Framework\MockObject\MockObject
     */
    private $metadata;

    /**
     * @var \Magento\Customer\Model\Session|\PHPUnit\Framework\MockObject\MockObject
     */
    private $sessionManager;

    protected function setUp(): void
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
        $observerMock = $this->createMock(\Magento\Framework\Event\Observer::class);
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

    /**
     * @return array
     */
    public function beforeStartDataProvider()
    {
        return [
            [true, 'once'],
            [false, 'never']
        ];
    }
}
