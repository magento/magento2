<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\HTTP\Test\Unit\Adapter;

use Magento\Framework\HTTP\Adapter\Curl;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CurlTest extends TestCase
{
    /**
     * @var Curl
     */
    protected $model;

    /**
     * @var MockObject|\StdClass
     */
    public static $curlMock;

    protected function setUp(): void
    {
        self::$curlMock = $this->getMockBuilder(\StdClass::class)
            ->addMethods(['setopt', 'exec'])
            ->getMock();
        require_once __DIR__ . '/_files/curl_exec_mock.php';
        $this->model = new Curl();
    }

    protected function tearDown(): void
    {
        self::$curlMock = null;
    }

    /**
     * @param string $response
     * @dataProvider readDataProvider
     */
    public function testRead($response)
    {
        self::$curlMock->expects($this->once())
            ->method('exec')
            ->willReturn($response);
        $this->assertEquals(file_get_contents(__DIR__ . '/_files/curl_response_expected.txt'), $this->model->read());
    }

    public function testReadFailure()
    {
        self::$curlMock->expects($this->once())
            ->method('exec')
            ->willReturn(false);
        $this->assertEquals('', $this->model->read());
    }

    /**
     * @param string $method
     * @param string $url
     * @param string $http_ver
     * @param array $headers
     * @param string $body
     * @param array $setopt
     * @return void
     * @dataProvider writeDataProvider
     */
    public function testWrite(
        string $method,
        string $url,
        string $http_ver,
        array $headers,
        string $body,
        array $setopt
    ): void {
        $defaultConfig = [];
        $configKeyToCurlProp = (new \ReflectionProperty(Curl::class, '_allowedParams'))->getValue($this->model);
        foreach ((new \ReflectionProperty(Curl::class, '_config'))->getValue($this->model) as $key => $value) {
            if (isset($configKeyToCurlProp[$key])) {
                $defaultConfig[] = [$configKeyToCurlProp[$key], $value];
            }
        }
        $setopt = array_merge(
            $defaultConfig,
            [
                [CURLOPT_URL, $url],
                [CURLOPT_RETURNTRANSFER, true],
            ],
            $setopt,
            [
                [CURLOPT_HEADER, true],
            ]
        );
        self::$curlMock->expects($this->exactly(count($setopt)))
            ->method('setopt')
            ->with(
                $this->isInstanceOf(\CurlHandle::class),
                $this->callback(
                    function (int $opt) use ($setopt) {
                        static $it = 0;
                        return $opt === $setopt[$it++][0];
                    }
                ),
                $this->callback(
                    function (mixed $val) use ($setopt) {
                        static $it = 0;
                        return $val === $setopt[$it++][1];
                    }
                )
            )
            ->willReturn(true);

        $this->model->write($method, $url, $http_ver, $headers, $body);
    }

    /**
     * @return array
     */
    public static function writeDataProvider()
    {
        return [
            'headers is empty' => [
                'POST',
                'http://example.com',
                '1.1',
                [],
                '{"key": "value"}',
                [
                    [CURLOPT_POST, true],
                    [CURLOPT_CUSTOMREQUEST, 'POST'],
                    [CURLOPT_POSTFIELDS, '{"key": "value"}'],
                    [CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1],
                    [CURLOPT_HTTPHEADER, []],
                ]
            ],
            'headers is an indexed array' => [
                'POST',
                'http://example.com',
                '1.1',
                ['Content-Type: application/json'],
                '{"key": "value"}',
                [
                    [CURLOPT_POST, true],
                    [CURLOPT_CUSTOMREQUEST, 'POST'],
                    [CURLOPT_POSTFIELDS, '{"key": "value"}'],
                    [CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1],
                    [CURLOPT_HTTPHEADER, ['Content-Type: application/json']],
                ]
            ],
            'headers is an associative array' => [
                'POST',
                'http://example.com',
                '1.1',
                ['Content-Type' => 'application/json'],
                '{"key": "value"}',
                [
                    [CURLOPT_POST, true],
                    [CURLOPT_CUSTOMREQUEST, 'POST'],
                    [CURLOPT_POSTFIELDS, '{"key": "value"}'],
                    [CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1],
                    [CURLOPT_HTTPHEADER, ['Content-Type: application/json']],
                ]
            ],
        ];
    }

    /**
     * @return array
     */
    public static function readDataProvider()
    {
        return [
            [file_get_contents(__DIR__ . '/_files/curl_response1.txt')],
            [file_get_contents(__DIR__ . '/_files/curl_response2.txt')],
        ];
    }
}
