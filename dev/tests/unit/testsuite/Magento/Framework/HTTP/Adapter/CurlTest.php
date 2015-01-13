<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\HTTP\Adapter;

class CurlTest extends \PHPUnit_Framework_TestCase
{
    /** @var Curl */
    protected $model;

    /** @var \Closure */
    public static $curlExectClosure;

    protected function setUp()
    {
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

    public function readDataProvider()
    {
        return [
            [file_get_contents(__DIR__ . '/_files/curl_response1.txt')],
            [file_get_contents(__DIR__ . '/_files/curl_response2.txt')],
        ];
    }
}

/**
 * Override global PHP function
 *
 * @SuppressWarnings("unused")
 * @param mixed $resource
 * @return string
 */
function curl_exec($resource)
{
    return call_user_func(CurlTest::$curlExectClosure);
}
