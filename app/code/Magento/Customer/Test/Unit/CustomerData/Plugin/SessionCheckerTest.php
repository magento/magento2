<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\CustomerData\Plugin;

use Magento\Customer\CustomerData\Plugin\SessionChecker;

class SessionCheckerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var SessionChecker
     */
    protected $plugin;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\PhpCookieManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $cookieManager;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $metadataFactory;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadata|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $metadata;

    /**
     * @var \Magento\Customer\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $sessionManager;

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

    public function beforeStartDataProvider()
    {
        return [
            [true, 'once'],
            [false, 'never']
        ];
    }
}
