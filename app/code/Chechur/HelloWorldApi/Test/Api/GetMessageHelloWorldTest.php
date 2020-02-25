<?php

declare(strict_types=1);

namespace Chechur\HelloWorldApi\Test\Api;

use Magento\Framework\Webapi\Rest\Request;
use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * Test cases related to check that service GetMessageHelloWorld works correctly
 * via web API.
 *
 * @see /V1/hello
 */
class GetMessageHelloWorldTest extends WebapiAbstract
{
    /**
     * Assert that correct message was returned.
     *
     * @return void
     */
    public function testGetMessage(): void
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/hello',
                'httpMethod' => Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => 'chechurHelloWorldApiGetMessageHelloWorldV1',
                'serviceVersion' => 'V1',
                'operation' => 'chechurHelloWorldApiGetMessageHelloWorldV1Execute',
            ],
        ];
        $this->assertEquals('<h1>__prefix__Hello World__suffix</h1>', $this->_webApiCall($serviceInfo));
    }
}
