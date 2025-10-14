<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Persistent\Test\Unit\Observer;

use Magento\Customer\Model\Session;
use Magento\Framework\Event\Observer;
use Magento\Framework\Session\SessionManager;
use Magento\Framework\Stdlib\Cookie\CookieMetadata;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\PhpCookieManager;
use Magento\Persistent\Observer\RefreshCustomerData;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RefreshCustomerDataTest extends TestCase
{
    /**
     * @var RefreshCustomerData
     */
    private $observer;

    /**
     * @var PhpCookieManager|MockObject
     */
    private $cookieManager;

    /**
     * @var CookieMetadataFactory|MockObject
     */
    private $metadataFactory;

    /**
     * @var CookieMetadata|MockObject
     */
    private $metadata;

    /**
     * @var Session|MockObject
     */
    private $sessionManager;

    protected function setUp(): void
    {
        $this->cookieManager = $this->getMockBuilder(PhpCookieManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->metadataFactory = $this->getMockBuilder(CookieMetadataFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->metadata = $this->getMockBuilder(CookieMetadata::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->sessionManager = $this->getMockBuilder(SessionManager::class)
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
        $observerMock = $this->createMock(Observer::class);
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
    public static function beforeStartDataProvider()
    {
        return [
            [true, 'once'],
            [false, 'never']
        ];
    }
}
