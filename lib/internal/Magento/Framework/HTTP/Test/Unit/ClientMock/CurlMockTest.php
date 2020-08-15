<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\HTTP\Test\Unit\ClientMock;

use Magento\Framework\HTTP\Test\Unit\ClientMock\Mock\CurlMock;
use PHPUnit\Framework\TestCase;

/**
 * Test HTTP client based on cUrl (mocked curl_exec).
 */
class CurlMockTest extends TestCase
{
    /**
     * @var CurlMock
     */
    protected $model;

    /**
     * @var \Closure
     */
    public static $curlExectClosure;

    protected function setUp(): void
    {
        require_once __DIR__ . '/_files/curl_exec_mock.php';
        $this->model = new CurlMock();
    }

    /**
     * Handle Curl response
     *
     * @param string $response
     * @return string
     */
    private function handleResponse(string $response): string
    {
        // Make sure we use valid newlines
        $response = explode("\r\n\r\n", str_replace("\n", "\r\n", $response), 2);

        // Parse headers
        $headers = explode("\r\n", $response[0]);
        foreach ($headers as $header) {
            call_user_func([$this->model, 'parseHeaders'], $this->model->getResource(), $header);
        }

        // Return body
        return $response[1] ?? '';
    }

    /**
     * Check that HTTP client parses cookies.
     *
     * @param string $response
     * @dataProvider cookiesDataProvider
     */
    public function testCookies($response)
    {
        self::$curlExectClosure = function () use ($response) {
            $this->handleResponse($response);
        };
        $this->model->get('http://127.0.0.1/test');
        $cookies = $this->model->getCookies();
        $this->assertIsArray($cookies);
        $this->assertEquals([
            'Normal' => 'OK',
            'Uppercase' => 'OK',
            'Lowercase' => 'OK',
        ], $cookies);
    }

    /**
     * @return array
     */
    public function cookiesDataProvider()
    {
        return [
            [file_get_contents(__DIR__ . '/_files/curl_response_cookies.txt')],
        ];
    }
}
