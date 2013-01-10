<?php
/**
 * Test SOAP fault model.
 *
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Webapi_Model_Soap_FaultTest extends PHPUnit_Framework_TestCase
{
    /** @var Mage_Webapi_Model_Soap_Fault */
    protected $_soapFault;

    protected function setUp()
    {
        /** Initialize SUT. */
        $this->_soapFault = new Mage_Webapi_Model_Soap_Fault();
        parent::setUp();
    }

    protected function tearDown()
    {
        unset($this->_soapFault);
        parent::tearDown();
    }

    public function testToXmlDeveloperModeOff()
    {
        $expectedResult = <<<XML
<?xml version="1.0" encoding="utf-8" ?>
<env:Envelope xmlns:env="http://www.w3.org/2003/05/soap-envelope">
    <env:Body>
        <env:Fault>
            <env:Code>
                <env:Value>env:Receiver</env:Value>
            </env:Code>
            <env:Reason>
                <env:Text xml:lang="en">Internal Error.</env:Text>
            </env:Reason>
        </env:Fault>
    </env:Body>
</env:Envelope>
XML;
        $actualXml = $this->_soapFault->toXml(false);
        $this->assertXmlStringEqualsXmlString(
            $expectedResult,
            $actualXml,
            'Wrong soap fault message with default parameters.'
        );
    }

    public function testToXmlDeveloperModeOn()
    {
        $actualXml = $this->_soapFault->toXml(true);
        $this->assertContains('<ExceptionTrace>', $actualXml, 'Exception trace not found in XML.');
    }

    /**
     * Test getSoapFaultMessage method.
     *
     * @dataProvider dataProviderForGetSoapFaultMessageTest
     */
    public function testGetSoapFaultMessage(
        $faultReason,
        $faultCode,
        $language,
        $additionalParameters,
        $expectedResult,
        $assertMessage
    ) {
        $actualResult = $this->_soapFault->getSoapFaultMessage(
            $faultReason,
            $faultCode,
            $language,
            $additionalParameters
        );
        $this->assertXmlStringEqualsXmlString($expectedResult, $actualResult, $assertMessage);
    }

    /**
     * Data provider for GetSoapFaultMessage test.
     *
     * @return array
     */
    public function dataProviderForGetSoapFaultMessageTest()
    {
        /** Include file with all expected soap fault XMLs. */
        $expectedXmls = include __DIR__ . '/../../_files/soap_fault/soap_fault_expected_xmls.php';
        return array(
            //Each array contains data for SOAP Fault Message, Expected XML and Assert Message.
            array(
                'Fault reason',
                'Sender',
                'cn',
                array('key1' => 'value1', 'key2' => 'value2'),
                $expectedXmls['expectedResultArrayDataDetails'],
                'Wrong soap fault message with associated array data details.'
            ),
            array(
                'Fault reason',
                'Sender',
                'en',
                array('value1', 'value2'),
                $expectedXmls['expectedResultIndexArrayDetails'],
                'Wrong soap fault message with index array data details.'
            ),
            array(
                'Fault reason',
                'Sender',
                'en',
                array(),
                $expectedXmls['expectedResultEmptyArrayDetails'],
                'Wrong soap fault message with empty array data details.'
            ),
            array(
                'Fault reason',
                'Sender',
                'en',
                (object)array('key' => 'value'),
                $expectedXmls['expectedResultObjectDetails'],
                'Wrong soap fault message with object data details.'
            ),
            array(
                'Fault reason',
                'Sender',
                'en',
                'String details',
                $expectedXmls['expectedResultStringDetails'],
                'Wrong soap fault message with string data details.'
            ),
            array(
                'Fault reason',
                'Sender',
                'en',
                array('key' => array('sub_key' => 'value')),
                $expectedXmls['expectedResultComplexDataDetails'],
                'Wrong soap fault message with complex data details.'
            ),
        );
    }
}
