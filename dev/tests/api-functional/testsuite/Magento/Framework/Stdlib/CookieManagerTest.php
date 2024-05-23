<?php
namespace Magento\Framework\Stdlib;

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\HttpClient\CurlClientWithCookies;

/**
 * End to end test of the Cookie Manager, using curl.
 *
 * Uses controllers in TestModule1 to set and delete cookies and verify 'Set-Cookie' headers that come back.
 */
class CookieManagerTest extends \Magento\TestFramework\TestCase\WebapiAbstract
{
    /**
     * @var string
     */
    private $cookieTesterUrl = 'testmoduleone/CookieTester';

    /** @var CurlClientWithCookies */
    protected $curlClient;

    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->curlClient = $objectManager->get(
            \Magento\TestFramework\TestCase\HttpClient\CurlClientWithCookies::class
        );
    }

    /**
     * Set a sensitive Cookie and delete it.
     *
     */
    public function testSensitiveCookie()
    {
        $url = $this->cookieTesterUrl . '/SetSensitiveCookie';
        $cookieParams =
            [
                'cookie_name' => 'test-sensitive-cookie',
                'cookie_value' => 'test-sensitive-cookie-value',
            ];
        $response = $this->curlClient->get($url, $cookieParams);

        $cookie = $this->findCookie($cookieParams['cookie_name'], $response['cookies']);
        $this->assertNotNull($cookie);
        $this->assertEquals($cookieParams['cookie_name'], $cookie['name']);
        $this->assertEquals($cookieParams['cookie_value'], $cookie['value']);
        $this->assertFalse(isset($cookie['domain']));
        $this->assertFalse(isset($cookie['path']));
        $this->assertEquals('true', $cookie['httponly']);
        $this->assertFalse(isset($cookie['secure']));
        $this->assertFalse(isset($cookie['max-age']));
    }

    /**
     * Set a public cookie
     *
     */
    public function testPublicCookieNameValue()
    {
        $url = $this->cookieTesterUrl . '/SetPublicCookie';
        $cookieParams =
            [
                'cookie_name' => 'test-cookie',
                'cookie_value' => 'test-cookie-value',
            ];

        $response = $this->curlClient->get($url, $cookieParams);

        $cookie = $this->findCookie($cookieParams['cookie_name'], $response['cookies']);
        $this->assertNotNull($cookie);
        $this->assertEquals($cookieParams['cookie_name'], $cookie['name']);
        $this->assertEquals($cookieParams['cookie_value'], $cookie['value']);
        $this->assertFalse(isset($cookie['domain']));
        $this->assertFalse(isset($cookie['path']));
        $this->assertFalse(isset($cookie['httponly']));
        $this->assertFalse(isset($cookie['secure']));
        $this->assertFalse(isset($cookie['max-age']));
    }

    /**
     * Set a public cookie
     *
     */
    public function testPublicCookieAll()
    {
        $url = $this->cookieTesterUrl . '/SetPublicCookie';
        $cookieParams =
            [
                'cookie_name' => 'test-cookie',
                'cookie_value' => 'test-cookie-value',
                'cookie_domain' => 'www.example.com',
                'cookie_path' => '/test/path',
                'cookie_httponly' => 'true',
                'cookie_secure' => 'true',
                'cookie_duration' => '600',
            ];

        $response = $this->curlClient->get($url, $cookieParams);

        $cookie = $this->findCookie($cookieParams['cookie_name'], $response['cookies']);
        $this->assertNotNull($cookie);
        $this->assertEquals($cookieParams['cookie_name'], $cookie['name']);
        $this->assertEquals($cookieParams['cookie_value'], $cookie['value']);
        $this->assertEquals($cookieParams['cookie_domain'], $cookie['domain']);
        $this->assertEquals($cookieParams['cookie_path'], $cookie['path']);
        $this->assertEquals($cookieParams['cookie_httponly'], $cookie['httponly']);
        $this->assertEquals($cookieParams['cookie_secure'], $cookie['secure']);
        if (isset($cookie['max-age'])) {
            $this->assertEquals($cookieParams['cookie_duration'], $cookie['max-age']);
        }
        $this->assertTrue(isset($cookie['expires']));
    }

    /**
     * Delete a cookie
     *
     */
    public function testDeleteCookie()
    {
        $url = $this->cookieTesterUrl . '/DeleteCookie';
        $cookieParams =
            [
                'cookie_name' => 'test-cookie',
                'cookie_value' => 'test-cookie-value',
            ];

        $response = $this->curlClient->get(
            $url,
            $cookieParams,
            ['Cookie: test-cookie=test-cookie-value; anothertestcookie=anothertestcookievalue']
        );

        $cookie = $this->findCookie($cookieParams['cookie_name'], $response['cookies']);
        $this->assertNotNull($cookie);
        $this->assertEquals($cookieParams['cookie_name'], $cookie['name']);
        $this->assertEquals('deleted', $cookie['value']);
        $this->assertFalse(isset($cookie['domain']));
        $this->assertFalse(isset($cookie['path']));
        $this->assertFalse(isset($cookie['httponly']));
        $this->assertFalse(isset($cookie['secure']));
        if (isset($cookie['max-age'])) {
            $this->assertEquals(0, $cookie['max-age']);
        }
        $this->assertEquals(
            date('D, j-M-o H:i:s T', strtotime('Thu, 01-Jan-1970 00:00:01 GMT')),
            date('D, j-M-o H:i:s T', strtotime($cookie['expires']))
        );
    }

    /**
     * Find cookie with given name in the list of cookies
     *
     * @param string $cookieName
     * @param array $cookies
     * @return $cookie|null
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    private function findCookie($cookieName, $cookies)
    {
        foreach ($cookies as $cookieIndex => $cookie) {
            if ($cookie['name'] === $cookieName) {
                return $cookie;
            }
        }
        return null;
    }
}
