<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\CustomerData\Plugin;

use Magento\Customer\CustomerData\Plugin\SessionChecker;
use Magento\Customer\Model\Session;
use Magento\Framework\Session\SessionManager;
use Magento\Framework\Stdlib\Cookie\CookieMetadata;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\PhpCookieManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SessionCheckerTest extends TestCase
{
    /**
     * @var SessionChecker
     */
    protected $plugin;

    /**
     * @var PhpCookieManager|MockObject
     */
    protected $cookieManager;

    /**
     * @var CookieMetadataFactory|MockObject
     */
    protected $metadataFactory;

    /**
     * @var CookieMetadata|MockObject
     */
    protected $metadata;

    /**
     * @var Session|MockObject
     */
    protected $sessionManager;

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

        $this->plugin = new SessionChecker($this->cookieManager, $this->metadataFactory);
    }

    /**
     * @param bool $result
     * @param string $callCount
     * @return void
     * @dataProvider beforeStartDataProvider
     */
    public function testBeforeStart($result, $callCount)
    {
        $phpSessionCookieName = 'PHPSESSID';
        $frontendSessionCookieName = 'mage-cache-sessid';
        $this->sessionManager->expects($this->once())
            ->method('getName')
            ->willReturn($phpSessionCookieName);
        $this->cookieManager->expects($this->exactly(2))
            ->method('getCookie')
            ->withConsecutive(
                [$phpSessionCookieName],
                [$frontendSessionCookieName]
            )
            ->willReturnOnConsecutiveCalls(false, $result);

        $this->metadataFactory->expects($this->{$callCount}())
            ->method('createCookieMetadata')
            ->willReturn($this->metadata);
        $this->metadata->expects($this->{$callCount}())
            ->method('setPath')
            ->with('/');
        $this->cookieManager->expects($this->{$callCount}())
            ->method('deleteCookie')
            ->with('mage-cache-sessid', $this->metadata);

        $this->plugin->beforeStart($this->sessionManager);
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
