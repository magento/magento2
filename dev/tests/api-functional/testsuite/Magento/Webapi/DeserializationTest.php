<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Webapi;

use Magento\TestFramework\TestCase\Webapi\Adapter\Rest\CurlClient;

class DeserializationTest extends \Magento\TestFramework\TestCase\WebapiAbstract
{
    /**
     * @var string
     */
    protected $_version;

    /**
     * @var string
     */
    protected $_restResourcePath;

    protected function setUp()
    {
        $this->_version = 'V1';
        $this->_restResourcePath = "/{$this->_version}/TestModule5/";
    }

    /**
     *  Test POST request with empty body
     */
    public function testPostRequestWithEmptyBody()
    {
        $this->_markTestAsRestOnly();
        $serviceInfo = [
            'rest' => [
                'resourcePath' => $this->_restResourcePath,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST,
            ],
        ];
        $expectedMessage = '{"message":"%fieldName is a required field.","parameters":{"fieldName":"item"}}';
        try {
            $this->_webApiCall($serviceInfo, CurlClient::EMPTY_REQUEST_BODY);
        } catch (\Exception $e) {
            $this->assertEquals(\Magento\Framework\Webapi\Exception::HTTP_BAD_REQUEST, $e->getCode());
            $this->assertContains(
                $expectedMessage,
                $e->getMessage(),
                "Response does not contain expected message."
            );
        }
    }

    /**
     *  Test PUT request with empty body
     */
    public function testPutRequestWithEmptyBody()
    {
        $this->_markTestAsRestOnly();
        $itemId = 1;
        $serviceInfo = [
            'rest' => [
                'resourcePath' => $this->_restResourcePath . $itemId,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_PUT,
            ],
        ];
        $expectedMessage = '{"message":"%fieldName is a required field.","parameters":{"fieldName":"entityItem"}}';
        try {
            $this->_webApiCall($serviceInfo, CurlClient::EMPTY_REQUEST_BODY);
        } catch (\Exception $e) {
            $this->assertEquals(\Magento\Framework\Webapi\Exception::HTTP_BAD_REQUEST, $e->getCode());
            $this->assertContains(
                $expectedMessage,
                $e->getMessage(),
                "Response does not contain expected message."
            );
        }
    }
}
