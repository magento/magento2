<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Webapi\Test\Unit\Model\Soap;

use \Magento\Webapi\Model\Soap\Fault;

/**
 * Test SOAP fault model.
 */
class FaultTest extends \PHPUnit_Framework_TestCase
{
    const WSDL_URL = 'http://host.com/?wsdl&services=customerV1';

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_requestMock;

    /** @var \Magento\Webapi\Model\Soap\Server */
    protected $_soapServerMock;

    /** @var \Magento\Webapi\Model\Soap\Fault */
    protected $_soapFault;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $_localeResolverMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_appStateMock;

    protected function setUp()
    {
        $this->_requestMock = $this->getMock('\Magento\Framework\App\RequestInterface');
        /** Initialize SUT. */
        $details = ['param1' => 'value1', 'param2' => 2];
        $code = 111;
        $webapiException = new \Magento\Framework\Webapi\Exception(
            __('Soap fault reason.'),
            $code,
            \Magento\Framework\Webapi\Exception::HTTP_INTERNAL_ERROR,
            $details
        );
        $this->_soapServerMock = $this->getMockBuilder(
            'Magento\Webapi\Model\Soap\Server'
        )->disableOriginalConstructor()->getMock();
        $this->_soapServerMock->expects($this->any())->method('generateUri')->will($this->returnValue(self::WSDL_URL));

        $this->_localeResolverMock = $this->getMockBuilder(
            'Magento\Framework\Locale\Resolver'
        )->disableOriginalConstructor()->getMock();
        $this->_localeResolverMock->expects(
            $this->any()
        )->method(
            'getLocale'
        )->will(
            $this->returnValue('en_US')
        );

        $this->_appStateMock = $this->getMock('\Magento\Framework\App\State', [], [], '', false);

        $this->_soapFault = new \Magento\Webapi\Model\Soap\Fault(
            $this->_requestMock,
            $this->_soapServerMock,
            $webapiException,
            $this->_localeResolverMock,
            $this->_appStateMock
        );
        parent::setUp();
    }

    protected function tearDown()
    {
        unset($this->_soapFault);
        unset($this->_requestMock);
        parent::tearDown();
    }

    public function testToXmlDeveloperModeOff()
    {
        $this->_appStateMock->expects($this->any())->method('getMode')->will($this->returnValue('production'));
        $wsdlUrl = urlencode(self::WSDL_URL);
        $expectedResult = <<<XML
<?xml version="1.0" encoding="utf-8" ?>
<env:Envelope xmlns:env="http://www.w3.org/2003/05/soap-envelope" xmlns:m="{$wsdlUrl}">
    <env:Body>
        <env:Fault>
            <env:Code>
                <env:Value>env:Receiver</env:Value>
            </env:Code>
            <env:Reason>
                <env:Text xml:lang="en">Soap fault reason.</env:Text>
            </env:Reason>
            <env:Detail>
                <m:GenericFault>
                    <m:Parameters>
                        <m:GenericFaultParameter>
                            <m:key>param1</m:key>
                            <m:value>value1</m:value>
                        </m:GenericFaultParameter>
                        <m:GenericFaultParameter>
                            <m:key>param2</m:key>
                            <m:value>2</m:value>
                        </m:GenericFaultParameter>
                    </m:Parameters>
                </m:GenericFault>
            </env:Detail>
        </env:Fault>
    </env:Body>
</env:Envelope>
XML;

        $actualXml = $this->_soapFault->toXml();
        $this->assertEquals(
            $this->_sanitizeXML($expectedResult),
            $this->_sanitizeXML($actualXml),
            'Wrong SOAP fault message with default parameters.'
        );
    }

    public function testToXmlDeveloperModeOn()
    {
        $this->_appStateMock->expects($this->any())->method('getMode')->will($this->returnValue('developer'));
        $actualXml = $this->_soapFault->toXml(true);
        $this->assertContains('<m:Trace>', $actualXml, 'Exception trace is not found in XML.');
    }

    /**
     * Test getSoapFaultMessage method.
     *
     * @dataProvider dataProviderForGetSoapFaultMessageTest
     */
    public function testGetSoapFaultMessage(
        $faultReason,
        $faultCode,
        $additionalParameters,
        $expectedResult,
        $assertMessage
    ) {
        $actualResult = $this->_soapFault->getSoapFaultMessage($faultReason, $faultCode, $additionalParameters);
        $wsdlUrl = urlencode(self::WSDL_URL);
        $this->assertEquals(
            $this->_sanitizeXML(str_replace('{wsdl_url}', $wsdlUrl, $expectedResult)),
            $this->_sanitizeXML($actualResult),
            $assertMessage
        );
    }

    /**
     * Data provider for GetSoapFaultMessage test.
     *
     * @return array
     */
    public function dataProviderForGetSoapFaultMessageTest()
    {
        /** Include file with all expected SOAP fault XMLs. */
        $expectedXmls = include __DIR__ . '/../../_files/soap_fault/soap_fault_expected_xmls.php';

        //Each array contains data for SOAP Fault Message, Expected XML, and Assert Message.
        return [
            'ArrayDataDetails' => [
                'Fault reason',
                'Sender',
                [
                    Fault::NODE_DETAIL_PARAMETERS => ['key1' => 'value1', 'key2' => 'value2', 'value3'],
                    Fault::NODE_DETAIL_TRACE => 'Trace',
                    'Invalid' => 'This node should be skipped'
                ],
                $expectedXmls['expectedResultArrayDataDetails'],
                'SOAP fault message with associated array data details is invalid.',
            ],
            'IndexArrayDetails' => [
                'Fault reason',
                'Sender',
                ['value1', 'value2'],
                $expectedXmls['expectedResultIndexArrayDetails'],
                'SOAP fault message with index array data details is invalid.',
            ],
            'EmptyArrayDetails' => [
                'Fault reason',
                'Sender',
                [],
                $expectedXmls['expectedResultEmptyArrayDetails'],
                'SOAP fault message with empty array data details is invalid.',
            ],
            'ObjectDetails' => [
                'Fault reason',
                'Sender',
                (object)['key' => 'value'],
                $expectedXmls['expectedResultObjectDetails'],
                'SOAP fault message with object data details is invalid.',
            ],
            'ComplexDataDetails' => [
                'Fault reason',
                'Sender',
                [Fault::NODE_DETAIL_PARAMETERS => ['key' => ['sub_key' => 'value']]],
                $expectedXmls['expectedResultComplexDataDetails'],
                'SOAP fault message with complex data details is invalid.',
            ]
        ];
    }

    public function testConstructor()
    {
        $message = "Soap fault reason.";
        $details = ['param1' => 'value1', 'param2' => 2];
        $code = 111;
        $webapiException = new \Magento\Framework\Webapi\Exception(
            __('Soap fault reason.'),
            $code,
            \Magento\Framework\Webapi\Exception::HTTP_INTERNAL_ERROR,
            $details
        );
        $soapFault = new \Magento\Webapi\Model\Soap\Fault(
            $this->_requestMock,
            $this->_soapServerMock,
            $webapiException,
            $this->_localeResolverMock,
            $this->_appStateMock
        );
        $actualXml = $soapFault->toXml();
        $wsdlUrl = urlencode(self::WSDL_URL);
        $expectedXml = <<<FAULT_XML
<?xml version="1.0" encoding="utf-8" ?>
<env:Envelope xmlns:env="http://www.w3.org/2003/05/soap-envelope" xmlns:m="{$wsdlUrl}">
    <env:Body>
        <env:Fault>
            <env:Code>
                <env:Value>env:Receiver</env:Value>
            </env:Code>
            <env:Reason>
                <env:Text xml:lang="en">{$message}</env:Text>
            </env:Reason>
            <env:Detail>
                <m:GenericFault>
                    <m:Parameters>
                        <m:GenericFaultParameter>
                            <m:key>param1</m:key>
                            <m:value>value1</m:value>
                        </m:GenericFaultParameter>
                        <m:GenericFaultParameter>
                            <m:key>param2</m:key>
                            <m:value>2</m:value>
                        </m:GenericFaultParameter>
                    </m:Parameters>
                </m:GenericFault>
            </env:Detail>
        </env:Fault>
    </env:Body>
</env:Envelope>
FAULT_XML;

        $this->assertEquals(
            $this->_sanitizeXML($expectedXml),
            $this->_sanitizeXML($actualXml),
            "Soap fault is invalid."
        );
    }

    /**
     * Convert XML to string.
     *
     * @param string $xmlString
     * @return string
     */
    protected function _sanitizeXML($xmlString)
    {
        $dom = new \DOMDocument(1.0);
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = false;
        // Only useful for "pretty" output with saveXML()
        $dom->loadXML($xmlString);
        // Must be done AFTER preserveWhiteSpace and formatOutput are set
        return $dom->saveXML();
    }
}
