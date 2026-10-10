<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Framework\HTTP\Test\Unit\Client;

use Magento\Framework\HTTP\Client\Curl;
use PHPUnit\Framework\TestCase;

/**
 * Test HTTP client based on cUrl.
 */
class CurlTest extends TestCase
{
    /**
     * Check that HTTP client can be used only for HTTP.
     */
    public function testInvalidProtocol()
    {
        $this->expectException('Exception');
        // Accommodate different libcurl version error messages:
        // - "Protocol telnet not supported or disabled in libcurl" (older versions)
        // - "Protocol \"telnet\" disabled" (newer versions)
        $this->expectExceptionMessageMatches('/Protocol .?telnet.? (not supported or )?disabled( in libcurl)?/');
        $client = new Curl();
        $client->get('telnet://127.0.0.1/test');
    }

    /**
     * Check the HTTP client ability to parse headers case-insensitive.
     */
    public function testParseHeaders()
    {
        // Prepare protected parseHeaders method
        $curl = new Curl();
        $parseHeaders = new \ReflectionMethod(
            $curl,
            'parseHeaders'
        );

        // Parse headers
        foreach ($this->headersDataProvider() as $header) {
            $parseHeaders->invoke($curl, null, $header);
        }

        // Validate headers
        $headers = $curl->getHeaders();
        $this->assertIsArray($headers);
        $this->assertEquals([
            'Content-Type' => 'text/html; charset=utf-8',
            'Set-Cookie' => [
                'Normal=OK',
                'Uppercase=OK',
                'Lowercase=OK',
            ]
        ], $headers);

        // Validate status
        $status = $curl->getStatus();
        $this->assertIsInt($status);
        $this->assertEquals(200, $status);

        // Validate cookies
        $cookies = $curl->getCookies();
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
    public function headersDataProvider()
    {
        return array_filter(explode(PHP_EOL, file_get_contents(__DIR__ . '/_files/curl_headers.txt')));
    }
}
