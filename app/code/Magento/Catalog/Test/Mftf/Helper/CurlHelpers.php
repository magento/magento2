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
     * @param string $formKey
     * @return void
     *
     */
    public function assertCurlResponseContainsString($url, $expectedString, $formKey = null): void
    {
        $cookie = $this->getCookie('admin');
        $curlResponse = $this->getCurlResponse($url, $cookie, $formKey);
        $this->assertStringContainsString($expectedString, $curlResponse);
    }

    /**
     * Sends a curl request with the provided URL & cookie. Returns the response
     *
     * @param string $url
     * @param string $cookie
     * @param string $formKey
     * @return string
     *
     */
    public function getCurlResponse($url, $cookie = null, $formKey = null): string
    {
        // Start Session
        $session = curl_init($url);

        // Set Options
        if ($formKey) {
            $data = [
                'form_key' => $formKey
            ];
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
    public function getCookie($cookieName = 'admin'): string
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
