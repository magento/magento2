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
        $this->expectExceptionMessageMatches('/Protocol .?telnet.? not supported or disabled in libcurl/');
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
     * @dataProvider httpMethodsDataProvider
     */
    public function testHttpMethod(string $method, array $args, string $expectedHttpMethod): void
    {
        $curl = new class () extends Curl {
            public string $lastMethod = '';
            public string $lastUri = '';
            public $lastParams = [];

            protected function makeRequest($method, $uri, $params = [])
            {
                $this->lastMethod = $method;
                $this->lastUri = $uri;
                $this->lastParams = $params;
            }
        };

        $curl->$method(...$args);

        $this->assertEquals($expectedHttpMethod, $curl->lastMethod);
        $this->assertEquals($args[0], $curl->lastUri);
        if (isset($args[1])) {
            $this->assertEquals($args[1], $curl->lastParams);
        }
    }

    /**
     * @return array
     */
    public static function httpMethodsDataProvider(): array
    {
        return [
            'put' => ['put', ['http://example.com', ['key' => 'value']], 'PUT'],
            'delete' => ['delete', ['http://example.com', ['key' => 'value']], 'DELETE'],
            'patch' => ['patch', ['http://example.com', ['key' => 'value']], 'PATCH'],
            'head' => ['head', ['http://example.com'], 'HEAD'],
            'options' => ['options', ['http://example.com'], 'OPTIONS'],
        ];
    }

    /**
     * @return array
     */
    public function headersDataProvider()
    {
        return array_filter(explode(PHP_EOL, file_get_contents(__DIR__ . '/_files/curl_headers.txt')));
    }
}
