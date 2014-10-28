<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
