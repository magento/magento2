<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Class to test Core Web API routing
 */
namespace Magento\Webapi\Routing;

use Magento\Framework\Webapi\Rest\Request;
use Magento\Integration\Model\Integration;
use Magento\TestFramework\Authentication\OauthHelper;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\Webapi\Adapter\Rest\RestClient;

class CoreRoutingTest extends BaseService
{
    public function testBasicRoutingExplicitPath()
    {
        $itemId = 1;
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/testmodule1/' . $itemId,
                'httpMethod' => Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => 'testModule1AllSoapAndRestV1',
                'operation' => 'testModule1AllSoapAndRestV1Item',
            ],
        ];
        $requestData = ['itemId' => $itemId];
        $item = $this->_webApiCall($serviceInfo, $requestData);
        $this->assertEquals('testProduct1', $item['name'], "Item was retrieved unsuccessfully");
    }

    public function testDisabledIntegrationAuthorizationException()
    {
        $itemId = 1;
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/testmodule1/' . $itemId,
                'httpMethod' => Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => 'testModule1AllSoapAndRestV1',
                'operation' => 'testModule1AllSoapAndRestV1Item',
            ],
        ];
        $requestData = ['itemId' => $itemId];

        /** Disable integration associated with active OAuth credentials. */
        $credentials = OauthHelper::getApiAccessCredentials();
        /** @var Integration $integration */
        $integration = $credentials['integration'];
        $originalStatus = $integration->getStatus();
        $integration->setStatus(Integration::STATUS_INACTIVE)->save();

        try {
            $this->assertUnauthorizedException($serviceInfo, $requestData);
        } catch (\Exception $e) {
            /** Restore original status of integration associated with active OAuth credentials */
            $integration->setStatus($originalStatus)->save();
            throw $e;
        }
        $integration->setStatus($originalStatus)->save();
    }

    public function testExceptionSoapInternalError()
    {
        $this->_markTestAsSoapOnly();
        $serviceInfo = [
            'soap' => [
                'service' => 'testModule3ErrorV1',
                'operation' => 'testModule3ErrorV1ServiceException',
            ],
        ];
        $this->expectException('SoapFault');
        $this->expectExceptionMessage('Generic service exception');
        $this->_webApiCall($serviceInfo);
    }

    public function testRestNoAcceptHeader()
    {
        $this->_markTestAsRestOnly();
        /** @var $curlClient RestClient */
        $curlClient = Bootstrap::getObjectManager()->get(
            RestClient::class
        );
        $response = $curlClient->get('/V1/testmodule1/resource1/1', [], ['Accept:']);
        $this->assertEquals('testProduct1', $response['name'], "Empty Accept header failed to return response.");
    }

    /**
     * Verifies that exception is thrown when the request contains unexpected parameters.
     *
     * @return void
     */
    public function testRequestParamsUnexpectedValueException(): void
    {
        $this->_markTestAsRestOnly();
        $expectedMessage = "Internal Error. Details are available in Magento log file. Report ID: webapi-";
        $unexpectedMessage = "\"%fieldName\" is required. Enter and try again.";

        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/testmodule1/withParam',
                'httpMethod' => Request::HTTP_METHOD_PUT,
            ],
        ];

        try {
            $this->_webApiCall($serviceInfo);
        } catch (\Exception $e) {
            $exceptionResult = $this->processRestExceptionResult($e);
            $actualMessage = $exceptionResult['message'];
            $this->assertStringNotContainsString($unexpectedMessage, $actualMessage);
            $this->assertStringContainsString($expectedMessage, $actualMessage);
        }
    }
}
