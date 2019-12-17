<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Example\ExampleApi\Test\Api;

use Magento\Framework\Webapi\Rest\Request;
use Magento\TestFramework\TestCase\WebapiAbstract;

class WelcomeMessageInterfaceTest extends WebapiAbstract
{
    /*
     * Check return method GET
     */
    public function testExecute()
    {
        $serviceInfo = [
                'rest' => [
                    'resourcePath' => '/V1/helloworld',
                    'httpMethod' => Request::HTTP_METHOD_GET,
                ],
            ];

        $response = $this->_webApiCall($serviceInfo);

        $this->assertTrue($response == "Hello World!");

    }

}
