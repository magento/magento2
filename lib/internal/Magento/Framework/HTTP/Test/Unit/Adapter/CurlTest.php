<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\HTTP\Test\Unit\Adapter;

use \Magento\Framework\HTTP\Adapter\Curl;

class CurlTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Curl
     */
    protected $model;

    /**
     * @var \Closure
     */
    public static $curlExectClosure;

    protected function setUp()
    {
        require_once __DIR__ . '/_files/curl_exec_mock.php';
        $this->model = new \Magento\Framework\HTTP\Adapter\Curl();
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
