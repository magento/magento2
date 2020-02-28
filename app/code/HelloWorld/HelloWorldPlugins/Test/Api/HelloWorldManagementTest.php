<?php

declare(strict_types=1);

namespace HelloWorld\HelloWorldPlugins\Test\Api;

use Magento\Framework\Webapi\Rest\Request;
use Magento\TestFramework\TestCase\WebapiAbstract;

class HelloWorldManagementTest extends WebapiAbstract
{
    /**#@+
     * Service constants
     */
    const RESOURCE_PATH = '/V1/helloworld';
    private $_actualResult;
    /**
     * @var string
     */
    private $_desiredResult;

    public function testGetList()
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH,
                'httpMethod' => Request::HTTP_METHOD_GET,
            ]
        ];

        $response = $this->_webApiCall($serviceInfo);
        $this->_actualResult = $response;
        $this->_desiredResult = "prefix<h1>Hello World</h1>suffix";
        $this->assertEquals($this->_desiredResult, $this->_actualResult);
    }
}
