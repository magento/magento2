<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Backend\Test\Mftf\Helper;

use Magento\FunctionalTestingFramework\Helper\Helper;

/**
 * Class for MFTF helpers for curl requests.
 */
class CurlHelpers extends Helper
{
    /**
     * Asserts that a curl request's response contains an expected string
     *
     * @param string $url
     * @param string $expectedString
     * @param string $postBody
     * @param string $cookieName
     * @param string $message
     * @return void
     *
     */
    public function assertCurlResponseContainsString($url, $expectedString, $postBody = null, $cookieName = 'admin', $message = ''): void
    {
        $cookie = $this->getCookie($cookieName);
        $curlResponse = $this->getCurlResponse($url, $cookie, $postBody);
        $this->assertStringContainsString($expectedString, $curlResponse, $message);
    }

    /**
     * Asserts that an MD5 encoded image retrieved via a curl request equals the expected string
     *
     * @param string $url
     * @param string $expectedString
     * @param string $postBody
     * @param string $cookieName
     * @param string $message
     * @return void
     *
     */
    public function assertImageContentIsEqual($url, $expectedString, $postBody = null, $cookieName = null, $message = ''): void
    {
        $cookie = $this->getCookie($cookieName);
        $imageContent = $this->getCurlResponse($url, $cookie, $postBody);
        // Must make request twice until bug is resolved: B2B-1789
        $imageContent = $this->getCurlResponse($url, $cookie, $postBody);
        // md5() here is not for cryptographic use.
        // phpcs:ignore Magento2.Security.InsecureFunction
        $imageContentMD5 = md5($imageContent);
        $this->assertStringContainsString($expectedString, $imageContentMD5, $message);
    }

    /**
     * Assert a that a curl request's response does not contain an expected string
     *
     * @param string $url
     * @param string $expectedString
     * @param string $postBody
     * @param string $cookieName
     * @return void
     *
     */
    public function assertCurlResponseDoesNotContainString($url, $expectedString, $postBody = null, $cookieName = 'admin'): void
    {
        $cookie = $this->getCookie($cookieName);
        $curlResponse = $this->getCurlResponse($url, $cookie, $postBody);
        $this->assertStringNotContainsString($expectedString, $curlResponse);
    }

    /**
     * Sends a curl request with the provided URL & cookie. Returns the response
     *
     * @param string $url
     * @param string $cookie
     * @param string $postBody
     * @return string
     *
     */
    private function getCurlResponse($url, $cookie = null, $postBody = null): string
    {
        // Start Session
        $session = curl_init($url);

        // Set Options
        if ($postBody) {
            $data = json_decode($postBody, true);
            curl_setopt($session, CURLOPT_POST, true);
            curl_setopt($session, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($session, CURLOPT_COOKIE, $cookie);
        curl_setopt($session, CURLOPT_RETURNTRANSFER, true);

        // Execute
        $response = curl_exec($session);
        curl_close($session);

        return $response;
    }

    /**
     * Gets the value of the specified cookie and returns the key value pair of the cookie
     *
     * @param string $cookieName
     * @return string
     *
     */
    private function getCookie($cookieName = 'admin'): string
    {
        try {
            $webDriver = $this->getModule('\Magento\FunctionalTestingFramework\Module\MagentoWebDriver');
            $cookieValue = $webDriver->grabCookie($cookieName);

            return $cookieName . '=' . $cookieValue;
        } catch (\Exception $exception) {
            $this->fail($exception->getMessage());
            return '';
        }
    }
}
