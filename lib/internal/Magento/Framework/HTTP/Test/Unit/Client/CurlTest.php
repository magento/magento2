<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\HTTP\Test\Unit\Client;

use \Magento\Framework\HTTP\Client\Curl;

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
        $this->model = new \Magento\Framework\HTTP\Client\Curl();
    }

    /**
     * @param string $response
     * @dataProvider readDataProvider
     */
    public function testGet($response)
    {
        self::$curlExectClosure = function () use ($response) {
            return $response;
        };

        $this->model->get('test_url');
        $this->assertEquals(file_get_contents(__DIR__ . '/_files/curl_response_expected.txt'), $this->model->getBody());
    }

    /**
     * @param string $response
     * @dataProvider readDataProvider
     */
    public function testPut($response)
    {
        self::$curlExectClosure = function () use ($response) {
            return $response;
        };

        $this->model->put('test_url', []);
        $this->assertEquals(file_get_contents(__DIR__ . '/_files/curl_response_expected.txt'), $this->model->getBody());
    }

    /**
     * @param string $response
     * @dataProvider readDataProvider
     */
    public function testPost($response)
    {
        self::$curlExectClosure = function () use ($response) {
            return $response;
        };

        $this->model->post('test_url', []);
        $this->assertEquals(file_get_contents(__DIR__ . '/_files/curl_response_expected.txt'), $this->model->getBody());
    }

    /**
     * @param string $response
     * @dataProvider readDataProvider
     */
    public function testDelete($response)
    {
        self::$curlExectClosure = function () use ($response) {
            return $response;
        };
        $this->model->delete('test_url');
        $this->assertEquals(file_get_contents(__DIR__ . '/_files/curl_response_expected.txt'), $this->model->getBody());
    }

    /**
     * @return array
     */
    public function readDataProvider()
    {
        return [
            [file_get_contents(__DIR__ . '/_files/curl_response.txt')],
        ];
    }
}
