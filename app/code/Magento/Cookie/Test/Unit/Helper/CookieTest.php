<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Cookie\Test\Unit\Helper;

use Magento\Cookie\Helper\Cookie;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Request\Http;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManager;
use Magento\Store\Model\Website;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Magento\Cookie\Helper\Cookie
 */
class CookieTest extends TestCase
{
    /**
     * @var Cookie
     */
    private $helper;

    /**
     * @var Http|MockObject
     */
    private $requestMock;

    /**
     * @var Context|MockObject
     */
    private $contextMock;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfigMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $storeMock = $this->createMock(Store::class);

        $websiteMock = $this->createMock(Website::class);
        $websiteMock->expects($this->any())->method('getId')->willReturn(1);

        $this->scopeConfigMock = $this->createPartialMock(ScopeConfigInterface::class, ['getValue', 'isSetFlag']);

        $this->requestMock = $this->createPartialMock(Http::class, ['getCookie']);

        $this->contextMock = $this->createPartialMock(Context::class, ['getRequest', 'getScopeConfig']);
        $this->contextMock->expects($this->once())
            ->method('getRequest')
            ->willReturn($this->requestMock);
        $this->contextMock->expects($this->once())
            ->method('getScopeConfig')
            ->willReturn($this->scopeConfigMock);

        $storeMangerMock = $this->createMock(StoreManager::class);

        $this->helper = (new ObjectManagerHelper($this))->getObject(
            Cookie::class,
            [
                'context' => $this->contextMock,
                'storeManger' => $storeMangerMock,
                'data' => [
                    'current_store' => $storeMock,
                    'website' => $websiteMock
                ]
            ]
        );
    }

    /**
     * Check cookie restriction notice allowed to display
     */
    public function testIsUserNotAllowSaveCookieAllowed(): void
    {
        $this->_getCookieStub([]);
        $this->_getConfigStub();

        $this->assertTrue($this->helper->isUserNotAllowSaveCookie());
    }

    /**
     * Test cookie restriction notice not allowed to display
     */
    public function testIsUserNotAllowSaveCookieNotAllowed(): void
    {
        $this->_getCookieStub([1 => 1]);
        $this->_getConfigStub();

        $this->assertFalse($this->helper->isUserNotAllowSaveCookie());
    }

    /**
     * Test serialized list of accepted save cookie website
     */
    public function testGetAcceptedSaveCookiesWebsiteIds(): void
    {
        $this->_getCookieStub([1 => 1]);

        $this->assertEquals($this->helper->getAcceptedSaveCookiesWebsiteIds(), json_encode([1 => 1]));
    }

    /**
     * Test get cookie restriction lifetime (in seconds)
     */
    public function testGetCookieRestrictionLifetime(): void
    {
        $this->_getConfigStub();

        $this->assertEquals($this->helper->getCookieRestrictionLifetime(), 60 * 60 * 24 * 365);
    }

    /**
     * Create config stub
     */
    private function _getConfigStub(): void
    {
        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->willReturnCallback([$this, 'getConfigMethodStub']);
    }

    /**
     * Generate getCookie stub for mock request object
     *
     * @param array $cookieString
     */
    private function _getCookieStub($cookieString = []): void
    {
        $this->requestMock->expects($this->any())
            ->method('getCookie')
            ->willReturn(json_encode($cookieString));
    }

    /**
     * Mock get config method
     * @static
     * @param string $hashName
     * @return string
     * @throws \InvalidArgumentException
     */
    public function getConfigMethodStub($hashName)
    {
        $defaultConfig = [
            'web/cookie/cookie_restriction' => 1,
            'web/cookie/cookie_restriction_lifetime' => 60 * 60 * 24 * 365,
        ];

        if (array_key_exists($hashName, $defaultConfig)) {
            return $defaultConfig[$hashName];
        }

        throw new \InvalidArgumentException('Unknown id = ' . $hashName);
    }
}
