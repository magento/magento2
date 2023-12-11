<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\HTTP\Test\Unit\Adapter;

use Magento\Framework\HTTP\Adapter\Curl;
use PHPUnit\Framework\TestCase;

class CurlTest extends TestCase
{
    /**
     * @var Curl
     */
    protected $model;

    /**
     * @var \Closure
     */
    public static $curlExectClosure;

    protected function setUp(): void
    {
        require_once __DIR__ . '/_files/curl_exec_mock.php';
        $this->model = new Curl();
    }

    /**
     * @param string $response
     * @dataProvider readDataProvider
     */
    public function testRead($response)
    {
        self::$curlExectClosure = function () use ($response) {
            return $response;
        };
        $this->assertEquals(file_get_contents(__DIR__ . '/_files/curl_response_expected.txt'), $this->model->read());
    }

    /**
     * @param string $response
     */
    public function testReadFailure()
    {
        self::$curlExectClosure = function () {
            return false;
        };
        $this->assertEquals('', $this->model->read());
    }

    /**
     * @return array
     */
    public function readDataProvider()
    {
        return [
            [file_get_contents(__DIR__ . '/_files/curl_response1.txt')],
            [file_get_contents(__DIR__ . '/_files/curl_response2.txt')],
        ];
    }
}
