<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TaxImportExport\Test\Mftf\Helper;

use Magento\FunctionalTestingFramework\Helper\Helper;

/**
 * Class for MFTF helpers for curl requests.
 */
class CurlHelpers extends Helper
{
    /**
     * Assert a that a curl request's response contains an expected string
     *
     * @param string $uri
     * @param string $expectedString
     * @return void
     *
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function assertCurlResponseContainsString($uri, $expectedString): void
    {
        $cookie = $this->getCookie();
        echo $cookie;
        $curlResponse = $this->getCurlResponse($uri, $cookie);
        echo $curlResponse;
        $this->assertStringContainsString($expectedString, $curlResponse);
    }

    /**
     * Sends a curl request with the provided URI & cookie. Returns the response
     *
     * @param string $uri
     * @param string $cookie
     * @return string
     *
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function getCurlResponse($uri, $cookie): string
    {
        try {
            // Start Session
            $session = curl_init($uri);

            // Set Options
            curl_setopt($session, CURLOPT_COOKIE, $cookie);
//            curl_setopt($session, CURLOPT_POST, true);
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
     * Sends a curl request with the provided URI & cookie. Returns the response in JSON format
     *
     * @return string
     *
     * @throws \Magento\Framework\Exception\FileSystemException
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
