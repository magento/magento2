<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Webapi\Routing;

use Magento\Framework\Exception\AuthorizationException;

/**
 * SOAP error handling test.
 */
class SoapErrorHandlingTest extends \Magento\TestFramework\TestCase\WebapiAbstract
{
    protected function setUp()
    {
        $this->_markTestAsSoapOnly();
        parent::setUp();
    }

    public function testWebapiException()
    {
        $serviceInfo = [
            'soap' => [
                'service' => 'testModule3ErrorV1',
                'operation' => 'testModule3ErrorV1WebapiException',
            ],
        ];
        try {
            $this->_webApiCall($serviceInfo);
            $this->fail("SoapFault was not raised as expected.");
        } catch (\SoapFault $e) {
            $this->checkSoapFault(
                $e,
                'Service not found',
                'env:Sender'
            );
        }
    }

    public function testUnknownException()
    {
        $serviceInfo = [
            'soap' => [
                'service' => 'testModule3ErrorV1',
                'operation' => 'testModule3ErrorV1OtherException',
            ],
        ];
        try {
            $this->_webApiCall($serviceInfo);
            $this->fail("SoapFault was not raised as expected.");
        } catch (\SoapFault $e) {
            /** In developer mode message is masked, so checks should be different in two modes */
            if (strpos($e->getMessage(), 'Internal Error') === false) {
                $this->checkSoapFault(
                    $e,
                    'Non service exception',
                    'env:Receiver',
                    null,
                    null,
                    'Magento\TestModule3\Service\V1\Error->otherException()'
                );
            } else {
                $this->checkSoapFault(
                    $e,
                    'Internal Error. Details are available in Magento log file. Report ID:',
                    'env:Receiver'
                );
            }
        }
    }

    public function testEmptyInputException()
    {
        $parameters = [];
        $this->_testWrappedError($parameters);
    }

    public function testSingleWrappedErrorException()
    {
        $parameters = [
            ['fieldName' => 'key1', 'value' => 'value1'],
        ];
        $this->_testWrappedError($parameters);
    }

    public function testMultipleWrappedErrorException()
    {
        $parameters = [
            ['fieldName' => 'key1', 'value' => 'value1'],
            ['fieldName' => 'key2', 'value' => 'value2'],
        ];
        $this->_testWrappedError($parameters);
    }

    public function testUnauthorized()
    {
        $serviceInfo = [
            'soap' => [
                'service' => 'testModule3ErrorV1',
                'operation' => 'testModule3ErrorV1AuthorizationException',
                'token' => 'invalidToken',
            ],
        ];

        $expectedException = new AuthorizationException(
            __(
                AuthorizationException::NOT_AUTHORIZED,
                ['resources' => 'Magento_TestModule3::resource1, Magento_TestModule3::resource2']
            )
        );

        try {
            $this->_webApiCall($serviceInfo);
            $this->fail("SoapFault was not raised as expected.");
        } catch (\SoapFault $e) {
            $this->checkSoapFault(
                $e,
                $expectedException->getRawMessage(),
                'env:Sender',
                $expectedException->getParameters() // expected error parameters
            );
        }
    }

    protected function _testWrappedError($parameters)
    {
        $serviceInfo = [
            'soap' => [
                'service' => 'testModule3ErrorV1',
                'operation' => 'testModule3ErrorV1InputException',
            ],
        ];

        $expectedException = new \Magento\Framework\Exception\InputException();
        foreach ($parameters as $error) {
            $expectedException->addError(
                __(\Magento\Framework\Exception\InputException::INVALID_FIELD_VALUE, $error)
            );
        }

        $arguments = [
            'wrappedErrorParameters' => $parameters,
        ];

        $expectedErrors = [];
        foreach ($expectedException->getErrors() as $key => $error) {
            $expectedErrors[$key] = [
                'message' => $error->getRawMessage(),
                'params' => $error->getParameters(),
            ];
        }

        try {
            $this->_webApiCall($serviceInfo, $arguments);
            $this->fail("SoapFault was not raised as expected.");
        } catch (\SoapFault $e) {
            $this->checkSoapFault(
                $e,
                $expectedException->getRawMessage(),
                'env:Sender',
                $expectedException->getParameters(), // expected error parameters
                $expectedErrors                      // expected wrapped errors
            );
        }
    }
}
