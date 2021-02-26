<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Mftf\Helper;

use Magento\FunctionalTestingFramework\Helper\Helper;

/**
 * Class for MFTF helpers for curl requests.
 */
class CurlHelpers extends Helper
{
    /**
     * Assert a that a curl request's response contains an expected string
     *
     * @param string $url
     * @param string $expectedString
     * @return void
     *
     */
    public function assertCurlResponseContainsString($url, $expectedString): void
    {
        $cookie = $this->getCookie('admin');
        $curlResponse = $this->getCurlResponse($url, $cookie);
        $this->assertStringContainsString($expectedString, $curlResponse);
    }

    /**
     * Sends a curl request with the provided URL & cookie. Returns the response
     *
     * @param string $url
     * @param string $cookie
     * @return string
     *
     */
    public function getCurlResponse($url, $cookie): string
    {
        try {
            // Start Session
            $session = curl_init($url);

            // Set Options
            curl_setopt($session, CURLOPT_COOKIE, $cookie);
            curl_setopt($session, CURLOPT_HEADER, false);
            curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($session, CURLOPT_FOLLOWLOCATION, true);

            // Execute
            $response = curl_exec($session);
            curl_close($session);

            return $response;
        } catch (\Exception $exception) {
            $this->fail($exception->getMessage());
        }
    }

    /**
     * Gets the value of the specified cookie and returns the key value pair of the cookie
     *
     * @param string $cookieName
     * @return string
     *
     */
    public function getCookie($cookieName = 'admin'): string
    {
        try {
            $webDriver = $this->getModule('\Magento\FunctionalTestingFramework\Module\MagentoWebDriver');
            $cookieValue = $webDriver->grabCookie($cookieName);

            return $cookieName . '=' . $cookieValue;
        } catch (\Exception $exception) {
            $this->fail($exception->getMessage());
        }
    }
}
