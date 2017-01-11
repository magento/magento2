<?php
/**
 * Test Web API error codes.
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Webapi\Routing;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\Webapi\Exception as WebapiException;

class RestErrorHandlingTest extends \Magento\TestFramework\TestCase\WebapiAbstract
{
    /**
     * @var string
     */
    protected $mode;

    protected function setUp()
    {
        $this->_markTestAsRestOnly();
        $this->mode = Bootstrap::getObjectManager()->get(\Magento\Framework\App\State::class)->getMode();
        parent::setUp();
    }

    public function testSuccess()
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/errortest/success',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
        ];

        $item = $this->_webApiCall($serviceInfo);

        // TODO: check Http Status = 200, cannot do yet due to missing header info returned

        $this->assertEquals('a good id', $item['value'], 'Success case is correct');
    }

    public function testNotFound()
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/errortest/notfound',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
        ];

        // \Magento\Framework\Api\ResourceNotFoundException
        $this->_errorTest(
            $serviceInfo,
            ['resourceY'],
            WebapiException::HTTP_NOT_FOUND,
            'Resource with ID "%1" not found.'
        );
    }

    public function testUnauthorized()
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/errortest/unauthorized',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
        ];

        // \Magento\Framework\Api\AuthorizationException
        $this->_errorTest(
            $serviceInfo,
            [],
            WebapiException::HTTP_UNAUTHORIZED,
            'Consumer is not authorized to access %1',
            ['resourceN']
        );
    }

    public function testOtherException()
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/errortest/otherException',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
        ];

        /* TODO : Fix as part MAGETWO-31330
        $expectedMessage = $this->mode == \Magento\Framework\App\State::MODE_DEVELOPER
            ? 'Non service exception'
            : 'Internal Error. Details are available in Magento log file. Report ID: webapi-XXX';
        */
        $expectedMessage = 'Internal Error. Details are available in Magento log file. Report ID: webapi-XXX';
        $this->_errorTest(
            $serviceInfo,
            [],
            WebapiException::HTTP_INTERNAL_ERROR,
            $expectedMessage,
            null,
            'Magento\TestModule3\Service\V1\Error->otherException()' // Check if trace contains proper error source
        );
    }

    /**
     * Perform a negative REST api call test case and compare the results with expected values.
     *
     * @param string $serviceInfo - REST Service information (i.e. resource path and HTTP method)
     * @param array $data - Data for the cal
     * @param int $httpStatus - Expected HTTP status
     * @param string|array $errorMessage - \Exception error message
     * @param array $parameters - Optional parameters array, or null if no parameters
     * @param string $traceString - Optional trace string to verify
     */
    protected function _errorTest(
        $serviceInfo,
        $data,
        $httpStatus,
        $errorMessage,
        $parameters = [],
        $traceString = null
    ) {
        try {
            $this->_webApiCall($serviceInfo, $data);
        } catch (\Exception $e) {
            $this->assertEquals($httpStatus, $e->getCode(), 'Checking HTTP status code');

            $body = json_decode($e->getMessage(), true);

            $errorMessages = is_array($errorMessage) ? $errorMessage : [$errorMessage];
            $actualMessage = $body['message'];
            $matches = [];
            //Report ID was created dynamically, so we need to replace it with some static value in order to test
            if (preg_match('/.*Report\sID\:\s([a-zA-Z0-9\-]*)/', $actualMessage, $matches)) {
                $actualMessage = str_replace($matches[1], 'webapi-XXX', $actualMessage);
            }
            //make sure that the match for a report with an id is found if Internal error was reported
            //Refer : \Magento\Framework\Webapi\ErrorProcessor::INTERNAL_SERVER_ERROR_MSG
            if (count($matches) > 1) {
                $this->assertTrue(!empty($matches[1]), 'Report id missing for internal error.');
            }
            $this->assertContains(
                $actualMessage,
                $errorMessages,
                "Message is invalid. Actual: '{$actualMessage}'. Expected one of: {'" . implode(
                    "', '",
                    $errorMessages
                ) . "'}"
            );

            if ($parameters) {
                $this->assertEquals($parameters, $body['parameters'], 'Checking body parameters');
            }

            if ($this->mode == \Magento\Framework\App\State::MODE_DEVELOPER && $traceString) {
                // TODO : Fix as part MAGETWO-31330
                //$this->assertContains($traceString, $body['trace'], 'Trace information is incorrect.');
            }
        }
    }
}
