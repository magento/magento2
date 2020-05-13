<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Stdlib\Cookie;

use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test PhpCookieManager
 *
 */
class PhpCookieManagerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Object Manager
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Cookie Manager
     *
     * @var \Magento\Framework\Stdlib\Cookie\PhpCookieManager
     */
    protected $cookieManager;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->cookieManager = $this->objectManager->create(\Magento\Framework\Stdlib\Cookie\PhpCookieManager::class);
    }

    public function testGetCookie()
    {
        $preTestCookies = $_COOKIE;
        $cookieName = 'cookie name';
        $cookieValue = 'cookie value';
        $defaultCookieValue = 'default';
        $_COOKIE[$cookieName] = $cookieValue;
        $this->assertEquals(
            $defaultCookieValue,
            $this->cookieManager->getCookie('unknown cookieName', $defaultCookieValue)
        );
        $this->assertEquals($cookieValue, $this->cookieManager->getCookie($cookieName, $defaultCookieValue));
        $this->assertEquals($defaultCookieValue, $this->cookieManager->getCookie(null, $defaultCookieValue));
        $this->assertNull($this->cookieManager->getCookie(null));
        $_COOKIE = $preTestCookies;
    }

    /**
     * It is not possible to write integration tests for CookieManager::setSensitiveCookie().
     * PHPUnit the following error when calling the function:
     *
     * PHPUnit\Framework\Error_Warning : Cannot modify header information - headers already sent
     */
    public function testSetSensitiveCookie()
    {
    }

    /**
     * It is not possible to write integration tests for CookieManager::setSensitiveCookie().
     * PHPUnit the following error when calling the function:
     *
     * PHPUnit\Framework\Error_Warning : Cannot modify header information - headers already sent
     */
    public function testSetPublicCookie()
    {
    }

    /**
     * It is not possible to write integration tests for CookieManager::deleteCookie().
     * PHPUnit the following error when calling the function:
     *
     * PHPUnit\Framework\Error_Warning : Cannot modify header information - headers already sent
     */
    public function testDeleteCookie()
    {
    }
}
