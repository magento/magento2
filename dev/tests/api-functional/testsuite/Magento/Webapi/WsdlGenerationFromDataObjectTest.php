<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Webapi;

use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test WSDL generation mechanisms.
 */
class WsdlGenerationFromDataObjectTest extends \Magento\TestFramework\TestCase\WebapiAbstract
{
    /** @var string */
    protected $_baseUrl = TESTS_BASE_URL;

    /** @var string */
    protected $_storeCode;

    /** @var string */
    protected $_soapUrl;

    protected function setUp()
    {
        $this->_markTestAsSoapOnly("WSDL generation tests are intended to be executed for SOAP adapter only.");
        $this->_storeCode = Bootstrap::getObjectManager()->get('Magento\Store\Model\StoreManagerInterface')
            ->getStore()->getCode();
        $this->_soapUrl = "{$this->_baseUrl}/soap/{$this->_storeCode}?services%3DtestModule5AllSoapAndRestV1%2CtestModule5AllSoapAndRestV2";
        parent::setUp();
    }

    public function testMultiServiceWsdl()
    {
        if (TESTS_WEB_API_ADAPTER == self::ADAPTER_SOAP) {
            $this->markTestIncomplete('MAGETWO-31016: incompatible with ZF 1.12.9');
        }
        $wsdlUrl = $this->_getBaseWsdlUrl() . 'testModule5AllSoapAndRestV1,testModule5AllSoapAndRestV2';
        $wsdlContent = $this->_convertXmlToString($this->_getWsdlContent($wsdlUrl));

        $this->_checkTypesDeclaration($wsdlContent);
        $this->_checkPortTypeDeclaration($wsdlContent);
        $this->_checkBindingDeclaration($wsdlContent);
        $this->_checkServiceDeclaration($wsdlContent);
        $this->_checkMessagesDeclaration($wsdlContent);
        $this->_checkFaultsDeclaration($wsdlContent);
    }

    public function testInvalidWsdlUrlNoServices()
    {
        $responseContent = $this->_getWsdlContent($this->_getBaseWsdlUrl());
        $this->assertContains("Requested services are missing.", $responseContent);
    }

    public function testInvalidWsdlUrlInvalidParameter()
    {
        $wsdlUrl = $this->_getBaseWsdlUrl() . '&invalid';
        $responseContent = $this->_getWsdlContent($wsdlUrl);
        $this->assertContains("Not allowed parameters", $responseContent);
    }

    /**
     * Remove unnecessary spaces and line breaks from xml string.
     *
     * @param string $xml
     * @return string
     */
    protected function _convertXmlToString($xml)
    {
        return str_replace(['    ', "\n", "\r", "&#13;", "&#10;"], '', $xml);
    }

    /**
     * Retrieve WSDL content.
     *
     * @param string $wsdlUrl
     * @return string|boolean
     */
    protected function _getWsdlContent($wsdlUrl)
    {
        $connection = curl_init($wsdlUrl);
        curl_setopt($connection, CURLOPT_RETURNTRANSFER, 1);
        $responseContent = curl_exec($connection);
        $responseDom = new \DOMDocument();
        $this->assertTrue(
            $responseDom->loadXML($responseContent),
            "Valid XML is always expected as a response for WSDL request."
        );
        return $responseContent;
    }

    /**
     * Generate base WSDL URL (without any services specified)
     *
     * @return string
     */
    protected function _getBaseWsdlUrl()
    {
        /** @var \Magento\TestFramework\TestCase\Webapi\Adapter\Soap $soapAdapter */
        $soapAdapter = $this->_getWebApiAdapter(self::ADAPTER_SOAP);
        $wsdlUrl = $soapAdapter->generateWsdlUrl([]);
        return $wsdlUrl;
    }

    /**
     * Ensure that types section has correct structure.
     *
     * @param string $wsdlContent
     */
    protected function _checkTypesDeclaration($wsdlContent)
    {
        // @codingStandardsIgnoreStart
        $typesSectionDeclaration = <<< TYPES_SECTION_DECLARATION
<types>
    <xsd:schema targetNamespace="{$this->_soapUrl}">
TYPES_SECTION_DECLARATION;
        // @codingStandardsIgnoreEnd
        $this->assertContains(
            $this->_convertXmlToString($typesSectionDeclaration),
            $wsdlContent,
            'Types section declaration is invalid'
        );
        $this->_checkElementsDeclaration($wsdlContent);
        $this->_checkComplexTypesDeclaration($wsdlContent);
    }

    /**
     * @param string $wsdlContent
     */
    protected function _checkElementsDeclaration($wsdlContent)
    {
        $requestElement = <<< REQUEST_ELEMENT
<xsd:element name="testModule5AllSoapAndRestV1ItemRequest" type="tns:TestModule5AllSoapAndRestV1ItemRequest"/>
REQUEST_ELEMENT;
        $this->assertContains(
            $this->_convertXmlToString($requestElement),
            $wsdlContent,
            'Request element declaration in types section is invalid'
        );
        $responseElement = <<< RESPONSE_ELEMENT
<xsd:element name="testModule5AllSoapAndRestV1ItemResponse" type="tns:TestModule5AllSoapAndRestV1ItemResponse"/>
RESPONSE_ELEMENT;
        $this->assertContains(
            $this->_convertXmlToString($responseElement),
            $wsdlContent,
            'Response element declaration in types section is invalid'
        );
    }

    /**
     * @param string $wsdlContent
     */
    protected function _checkComplexTypesDeclaration($wsdlContent)
    {
        // @codingStandardsIgnoreStart
        $requestType = <<< REQUEST_TYPE
<xsd:complexType name="TestModule5AllSoapAndRestV1ItemRequest">
    <xsd:annotation>
        <xsd:documentation>Retrieve an item.</xsd:documentation>
        <xsd:appinfo xmlns:inf="{$this->_soapUrl}"/>
    </xsd:annotation>
    <xsd:sequence>
        <xsd:element name="entityId" minOccurs="1" maxOccurs="1" type="xsd:int">
            <xsd:annotation>
                <xsd:documentation></xsd:documentation>
                <xsd:appinfo xmlns:inf="{$this->_soapUrl}">
                    <inf:min/>
                    <inf:max/>
                    <inf:callInfo>
                        <inf:callName>testModule5AllSoapAndRestV1Item</inf:callName>
                        <inf:requiredInput>Yes</inf:requiredInput>
                    </inf:callInfo>
                </xsd:appinfo>
            </xsd:annotation>
        </xsd:element>
    </xsd:sequence>
</xsd:complexType>
REQUEST_TYPE;
        // @codingStandardsIgnoreEnd
        $this->assertContains(
            $this->_convertXmlToString($requestType),
            $wsdlContent,
            'Request type declaration in types section is invalid'
        );
        // @codingStandardsIgnoreStart
        $responseType = <<< RESPONSE_TYPE
<xsd:complexType name="TestModule5AllSoapAndRestV1ItemResponse">
    <xsd:annotation>
        <xsd:documentation>
            Response container for the testModule5AllSoapAndRestV1Item call.
        </xsd:documentation>
        <xsd:appinfo xmlns:inf="{$this->_soapUrl}"/>
    </xsd:annotation>
    <xsd:sequence>
        <xsd:element name="result" minOccurs="1" maxOccurs="1" type="tns:TestModule5V1EntityAllSoapAndRest">
            <xsd:annotation>
                <xsd:documentation></xsd:documentation>
                <xsd:appinfo xmlns:inf="{$this->_soapUrl}">
                    <inf:callInfo>
                        <inf:callName>testModule5AllSoapAndRestV1Item</inf:callName>
                        <inf:returned>Always</inf:returned>
                    </inf:callInfo>
                </xsd:appinfo>
            </xsd:annotation>
        </xsd:element>
    </xsd:sequence>
</xsd:complexType>
RESPONSE_TYPE;
        // @codingStandardsIgnoreEnd
        $this->assertContains(
            $this->_convertXmlToString($responseType),
            $wsdlContent,
            'Response type declaration in types section is invalid'
        );
        $this->_checkReferencedTypeDeclaration($wsdlContent);
    }

    /**
     * Ensure that complex type generated from Data Object is correct.
     *
     * @param string $wsdlContent
     */
    protected function _checkReferencedTypeDeclaration($wsdlContent)
    {
        // @codingStandardsIgnoreStart
        $referencedType = <<< RESPONSE_TYPE
<xsd:complexType name="TestModule5V1EntityAllSoapAndRest">
    <xsd:annotation>
        <xsd:documentation>Some Data Object short description. Data Object long multi line description.</xsd:documentation>
        <xsd:appinfo xmlns:inf="{$this->_soapUrl}"/>
    </xsd:annotation>
    <xsd:sequence>
        <xsd:element name="entityId" minOccurs="1" maxOccurs="1" type="xsd:int">
            <xsd:annotation>
                <xsd:documentation>Item ID</xsd:documentation>
                <xsd:appinfo xmlns:inf="{$this->_soapUrl}">
                    <inf:min/>
                    <inf:max/>
                    <inf:callInfo>
                        <inf:callName>testModule5AllSoapAndRestV1Item</inf:callName>
                        <inf:callName>testModule5AllSoapAndRestV1Create</inf:callName>
                        <inf:callName>testModule5AllSoapAndRestV1Update</inf:callName>
                        <inf:callName>testModule5AllSoapAndRestV1NestedUpdate</inf:callName>
                        <inf:returned>Always</inf:returned>
                    </inf:callInfo>
                    <inf:callInfo>
                        <inf:callName>testModule5AllSoapAndRestV1Create</inf:callName>
                        <inf:callName>testModule5AllSoapAndRestV1Update</inf:callName>
                        <inf:callName>testModule5AllSoapAndRestV1NestedUpdate</inf:callName>
                        <inf:requiredInput>Yes</inf:requiredInput>
                    </inf:callInfo>
                </xsd:appinfo>
            </xsd:annotation>
        </xsd:element>
        <xsd:element name="name" minOccurs="0" maxOccurs="1" type="xsd:string">
            <xsd:annotation>
                <xsd:documentation>Item name</xsd:documentation>
                <xsd:appinfo xmlns:inf="{$this->_soapUrl}">
                    <inf:maxLength/>
                    <inf:callInfo>
                        <inf:callName>testModule5AllSoapAndRestV1Item</inf:callName>
                        <inf:callName>testModule5AllSoapAndRestV1Create</inf:callName>
                        <inf:callName>testModule5AllSoapAndRestV1Update</inf:callName>
                        <inf:callName>testModule5AllSoapAndRestV1NestedUpdate</inf:callName>
                        <inf:returned>Conditionally</inf:returned>
                    </inf:callInfo>
                    <inf:callInfo>
                        <inf:callName>testModule5AllSoapAndRestV1Create</inf:callName>
                        <inf:callName>testModule5AllSoapAndRestV1Update</inf:callName>
                        <inf:callName>testModule5AllSoapAndRestV1NestedUpdate</inf:callName>
                        <inf:requiredInput>No</inf:requiredInput>
                    </inf:callInfo>
                </xsd:appinfo>
            </xsd:annotation>
        </xsd:element>
        <xsd:element name="enabled" minOccurs="1" maxOccurs="1" type="xsd:boolean">
            <xsd:annotation>
                <xsd:documentation></xsd:documentation>
                <xsd:appinfo xmlns:inf="{$this->_soapUrl}">
                    <inf:default>false</inf:default>
                    <inf:callInfo>
                        <inf:callName>testModule5AllSoapAndRestV1Item</inf:callName>
                        <inf:callName>testModule5AllSoapAndRestV1Create</inf:callName>
                        <inf:callName>testModule5AllSoapAndRestV1Update</inf:callName>
                        <inf:callName>testModule5AllSoapAndRestV1NestedUpdate</inf:callName>
                        <inf:returned>Conditionally</inf:returned>
                    </inf:callInfo>
                    <inf:callInfo>
                        <inf:callName>testModule5AllSoapAndRestV1Create</inf:callName>
                        <inf:callName>testModule5AllSoapAndRestV1Update</inf:callName>
                        <inf:callName>testModule5AllSoapAndRestV1NestedUpdate</inf:callName>
                        <inf:requiredInput>No</inf:requiredInput>
                    </inf:callInfo>
                </xsd:appinfo>
            </xsd:annotation>
        </xsd:element>
        <xsd:element name="orders" minOccurs="1" maxOccurs="1" type="xsd:boolean">
            <xsd:annotation>
                <xsd:documentation></xsd:documentation>
                <xsd:appinfo xmlns:inf="{$this->_soapUrl}">
                    <inf:default>false</inf:default>
                    <inf:callInfo>
                        <inf:callName>testModule5AllSoapAndRestV1Item</inf:callName>
                        <inf:callName>testModule5AllSoapAndRestV1Create</inf:callName>
                        <inf:callName>testModule5AllSoapAndRestV1Update</inf:callName>
                        <inf:callName>testModule5AllSoapAndRestV1NestedUpdate</inf:callName>
                        <inf:returned>Conditionally</inf:returned>
                    </inf:callInfo>
                    <inf:callInfo>
                        <inf:callName>testModule5AllSoapAndRestV1Create</inf:callName>
                        <inf:callName>testModule5AllSoapAndRestV1Update</inf:callName>
                        <inf:callName>testModule5AllSoapAndRestV1NestedUpdate</inf:callName>
                        <inf:requiredInput>No</inf:requiredInput>
                    </inf:callInfo>
                </xsd:appinfo>
            </xsd:annotation>
        </xsd:element>
        <xsd:element name="customAttributes" type="tns:ArrayOfFrameworkAttributeInterface" minOccurs="0">
            <xsd:annotation>
                <xsd:documentation></xsd:documentation>
                <xsd:appinfo xmlns:inf="{$this->_soapUrl}">
                    <inf:natureOfType>array</inf:natureOfType>
                    <inf:callInfo>
                        <inf:callName>testModule5AllSoapAndRestV1Item</inf:callName>
                        <inf:callName>testModule5AllSoapAndRestV1Create</inf:callName>
                        <inf:callName>testModule5AllSoapAndRestV1Update</inf:callName>
                        <inf:callName>testModule5AllSoapAndRestV1NestedUpdate</inf:callName>
                        <inf:returned>Conditionally</inf:returned>
                    </inf:callInfo>
                    <inf:callInfo>
                        <inf:callName>testModule5AllSoapAndRestV1Create</inf:callName>
                        <inf:callName>testModule5AllSoapAndRestV1Update</inf:callName>
                        <inf:callName>testModule5AllSoapAndRestV1NestedUpdate</inf:callName>
                        <inf:requiredInput>No</inf:requiredInput>
                    </inf:callInfo>
                </xsd:appinfo>
            </xsd:annotation>
    </xsd:element>
    </xsd:sequence>
</xsd:complexType>
RESPONSE_TYPE;
        // @codingStandardsIgnoreEnd
        $this->assertContains(
            $this->_convertXmlToString($referencedType),
            $wsdlContent,
            'Declaration of complex type generated from Data Object, which is referenced in response, is invalid'
        );
    }

    /**
     * Ensure that port type sections have correct structure.
     *
     * @param string $wsdlContent
     */
    protected function _checkPortTypeDeclaration($wsdlContent)
    {
        $firstPortType = <<< FIRST_PORT_TYPE
<portType name="testModule5AllSoapAndRestV1PortType">
FIRST_PORT_TYPE;
        $this->assertContains(
            $this->_convertXmlToString($firstPortType),
            $wsdlContent,
            'Port type declaration is missing or invalid'
        );
        $secondPortType = <<< SECOND_PORT_TYPE
<portType name="testModule5AllSoapAndRestV2PortType">
SECOND_PORT_TYPE;
        $this->assertContains(
            $this->_convertXmlToString($secondPortType),
            $wsdlContent,
            'Port type declaration is missing or invalid'
        );
        $operationDeclaration = <<< OPERATION_DECLARATION
<operation name="testModule5AllSoapAndRestV2Item">
    <input message="tns:testModule5AllSoapAndRestV2ItemRequest"/>
    <output message="tns:testModule5AllSoapAndRestV2ItemResponse"/>
    <fault name="GenericFault" message="tns:GenericFault"/>
</operation>
<operation name="testModule5AllSoapAndRestV2Items">
    <input message="tns:testModule5AllSoapAndRestV2ItemsRequest"/>
    <output message="tns:testModule5AllSoapAndRestV2ItemsResponse"/>
    <fault name="GenericFault" message="tns:GenericFault"/>
</operation>
OPERATION_DECLARATION;
        $this->assertContains(
            $this->_convertXmlToString($operationDeclaration),
            $wsdlContent,
            'Operation in port type is invalid'
        );
    }

    /**
     * Ensure that binding sections have correct structure.
     *
     * @param string $wsdlContent
     */
    protected function _checkBindingDeclaration($wsdlContent)
    {
        $firstBinding = <<< FIRST_BINDING
<binding name="testModule5AllSoapAndRestV1Binding" type="tns:testModule5AllSoapAndRestV1PortType">
    <soap12:binding style="document" transport="http://schemas.xmlsoap.org/soap/http"/>
FIRST_BINDING;
        $this->assertContains(
            $this->_convertXmlToString($firstBinding),
            $wsdlContent,
            'Binding declaration is missing or invalid'
        );
        $secondBinding = <<< SECOND_BINDING
<binding name="testModule5AllSoapAndRestV2Binding" type="tns:testModule5AllSoapAndRestV2PortType">
    <soap12:binding style="document" transport="http://schemas.xmlsoap.org/soap/http"/>
SECOND_BINDING;
        $this->assertContains(
            $this->_convertXmlToString($secondBinding),
            $wsdlContent,
            'Binding declaration is missing or invalid'
        );
        $operationDeclaration = <<< OPERATION_DECLARATION
<operation name="testModule5AllSoapAndRestV1Item">
    <soap:operation soapAction="testModule5AllSoapAndRestV1Item"/>
    <input>
        <soap12:body use="literal"/>
    </input>
    <output>
        <soap12:body use="literal"/>
    </output>
    <fault name="GenericFault">
        <soap12:fault use="literal" name="GenericFault"/>
    </fault>
</operation>
<operation name="testModule5AllSoapAndRestV1Items">
    <soap:operation soapAction="testModule5AllSoapAndRestV1Items"/>
    <input>
        <soap12:body use="literal"/>
    </input>
    <output>
        <soap12:body use="literal"/>
    </output>
    <fault name="GenericFault">
        <soap12:fault use="literal" name="GenericFault"/>
    </fault>
</operation>
OPERATION_DECLARATION;
        $this->assertContains(
            $this->_convertXmlToString($operationDeclaration),
            $wsdlContent,
            'Operation in binding is invalid'
        );
    }

    /**
     * Ensure that service sections have correct structure.
     *
     * @param string $wsdlContent
     */
    protected function _checkServiceDeclaration($wsdlContent)
    {
        // @codingStandardsIgnoreStart
        $firstServiceDeclaration = <<< FIRST_SERVICE_DECLARATION
<service name="testModule5AllSoapAndRestV1Service">
    <port name="testModule5AllSoapAndRestV1Port" binding="tns:testModule5AllSoapAndRestV1Binding">
        <soap:address location="{$this->_baseUrl}/soap/{$this->_storeCode}?services=testModule5AllSoapAndRestV1%2CtestModule5AllSoapAndRestV2"/>
    </port>
</service>
FIRST_SERVICE_DECLARATION;
        // @codingStandardsIgnoreEnd
        $this->assertContains(
            $this->_convertXmlToString($firstServiceDeclaration),
            $wsdlContent,
            'First service section is invalid'
        );

        // @codingStandardsIgnoreStart
        $secondServiceDeclaration = <<< SECOND_SERVICE_DECLARATION
<service name="testModule5AllSoapAndRestV2Service">
    <port name="testModule5AllSoapAndRestV2Port" binding="tns:testModule5AllSoapAndRestV2Binding">
        <soap:address location="{$this->_baseUrl}/soap/{$this->_storeCode}?services=testModule5AllSoapAndRestV1%2CtestModule5AllSoapAndRestV2"/>
    </port>
</service>
SECOND_SERVICE_DECLARATION;
        // @codingStandardsIgnoreEnd
        $this->assertContains(
            $this->_convertXmlToString($secondServiceDeclaration),
            $wsdlContent,
            'Second service section is invalid'
        );
    }

    /**
     * Ensure that messages sections have correct structure.
     *
     * @param string $wsdlContent
     */
    protected function _checkMessagesDeclaration($wsdlContent)
    {
        $itemMessagesDeclaration = <<< MESSAGES_DECLARATION
<message name="testModule5AllSoapAndRestV2ItemRequest">
    <part name="messageParameters" element="tns:testModule5AllSoapAndRestV2ItemRequest"/>
</message>
<message name="testModule5AllSoapAndRestV2ItemResponse">
    <part name="messageParameters" element="tns:testModule5AllSoapAndRestV2ItemResponse"/>
</message>
MESSAGES_DECLARATION;
        $this->assertContains(
            $this->_convertXmlToString($itemMessagesDeclaration),
            $wsdlContent,
            'Messages section for "item" operation is invalid'
        );
        $itemsMessagesDeclaration = <<< MESSAGES_DECLARATION
<message name="testModule5AllSoapAndRestV2ItemsRequest">
    <part name="messageParameters" element="tns:testModule5AllSoapAndRestV2ItemsRequest"/>
</message>
<message name="testModule5AllSoapAndRestV2ItemsResponse">
    <part name="messageParameters" element="tns:testModule5AllSoapAndRestV2ItemsResponse"/>
</message>
MESSAGES_DECLARATION;
        $this->assertContains(
            $this->_convertXmlToString($itemsMessagesDeclaration),
            $wsdlContent,
            'Messages section for "items" operation is invalid'
        );
    }

    /**
     * Ensure that SOAP faults are declared properly.
     *
     * @param string $wsdlContent
     */
    protected function _checkFaultsDeclaration($wsdlContent)
    {
        $this->_checkFaultsPortTypeSection($wsdlContent);
        $this->_checkFaultsBindingSection($wsdlContent);
        $this->_checkFaultsMessagesSection($wsdlContent);
        $this->_checkFaultsComplexTypeSection($wsdlContent);
    }

    /**
     * @param string $wsdlContent
     */
    protected function _checkFaultsPortTypeSection($wsdlContent)
    {
        $faultsInPortType = <<< FAULT_IN_PORT_TYPE
<fault name="GenericFault" message="tns:GenericFault"/>
FAULT_IN_PORT_TYPE;
        $this->assertContains(
            $this->_convertXmlToString($faultsInPortType),
            $wsdlContent,
            'SOAP Fault section in port type section is invalid'
        );
    }

    /**
     * @param string $wsdlContent
     */
    protected function _checkFaultsBindingSection($wsdlContent)
    {
        $faultsInBinding = <<< FAULT_IN_BINDING
<fault name="GenericFault">
    <soap12:fault use="literal" name="GenericFault"/>
</fault>
FAULT_IN_BINDING;
        $this->assertContains(
            $this->_convertXmlToString($faultsInBinding),
            $wsdlContent,
            'SOAP Fault section in binding section is invalid'
        );
    }

    /**
     * @param string $wsdlContent
     */
    protected function _checkFaultsMessagesSection($wsdlContent)
    {
        $genericFaultMessage = <<< GENERIC_FAULT_IN_MESSAGES
<message name="GenericFault">
    <part name="messageParameters" element="tns:GenericFault"/>
</message>
GENERIC_FAULT_IN_MESSAGES;
        $this->assertContains(
            $this->_convertXmlToString($genericFaultMessage),
            $wsdlContent,
            'Generic SOAP Fault declaration in messages section is invalid'
        );
    }

    /**
     * @param string $wsdlContent
     */
    protected function _checkFaultsComplexTypeSection($wsdlContent)
    {
        $this->assertContains(
            $this->_convertXmlToString('<xsd:element name="GenericFault" type="tns:GenericFault"/>'),
            $wsdlContent,
            'Default SOAP Fault complex type element declaration is invalid'
        );

        // @codingStandardsIgnoreStart
        $genericFaultType = <<< GENERIC_FAULT_COMPLEX_TYPE
<xsd:complexType name="GenericFault">
    <xsd:sequence>
        <xsd:element name="Trace" minOccurs="0" maxOccurs="1" type="xsd:string">
            <xsd:annotation>
                <xsd:documentation>Exception calls stack trace.</xsd:documentation>
                <xsd:appinfo xmlns:inf="{$this->_soapUrl}">
                    <inf:maxLength/>
                </xsd:appinfo>
            </xsd:annotation>
        </xsd:element>
        <xsd:element name="Parameters" type="tns:ArrayOfGenericFaultParameter" minOccurs="0">
            <xsd:annotation>
                <xsd:documentation>Additional exception parameters.</xsd:documentation>
                <xsd:appinfo xmlns:inf="{$this->_soapUrl}">
                    <inf:natureOfType>array</inf:natureOfType>
                </xsd:appinfo>
            </xsd:annotation>
        </xsd:element>
        <xsd:element name="WrappedErrors" type="tns:ArrayOfWrappedError" minOccurs="0">
            <xsd:annotation>
                <xsd:documentation>Additional wrapped errors.</xsd:documentation>
                <xsd:appinfo xmlns:inf="{$this->_soapUrl}">
                    <inf:natureOfType>array</inf:natureOfType>
                </xsd:appinfo>
            </xsd:annotation>
        </xsd:element>
    </xsd:sequence>
</xsd:complexType>
GENERIC_FAULT_COMPLEX_TYPE;
        $this->assertContains(
            $this->_convertXmlToString($genericFaultType),
            $wsdlContent,
            'Default SOAP Fault complex types declaration is invalid'
        );

        $detailsParameterType = <<< PARAM_COMPLEX_TYPE
<xsd:complexType name="GenericFaultParameter">
    <xsd:sequence>
        <xsd:element name="key" minOccurs="1" maxOccurs="1" type="xsd:string">
            <xsd:annotation>
                <xsd:documentation></xsd:documentation>
                <xsd:appinfo xmlns:inf="{$this->_soapUrl}">
                    <inf:maxLength/>
                </xsd:appinfo>
            </xsd:annotation>
        </xsd:element>
        <xsd:element name="value" minOccurs="1" maxOccurs="1" type="xsd:string">
            <xsd:annotation>
                <xsd:documentation></xsd:documentation>
                <xsd:appinfo xmlns:inf="{$this->_soapUrl}">
                    <inf:maxLength/>
                </xsd:appinfo>
            </xsd:annotation>
        </xsd:element>
    </xsd:sequence>
</xsd:complexType>
PARAM_COMPLEX_TYPE;
        $this->assertContains(
            $this->_convertXmlToString($detailsParameterType),
            $wsdlContent,
            'Details parameter complex types declaration is invalid.'
        );

        $detailsWrappedErrorType = <<< WRAPPED_ERROR_COMPLEX_TYPE
<xsd:complexType name="WrappedError">
    <xsd:sequence>
        <xsd:element name="message" minOccurs="1" maxOccurs="1" type="xsd:string">
            <xsd:annotation>
                <xsd:documentation></xsd:documentation>
                <xsd:appinfo xmlns:inf="{$this->_baseUrl}/soap/{$this->_storeCode}?services%3DtestModule5AllSoapAndRestV1%2CtestModule5AllSoapAndRestV2">
                    <inf:maxLength/>
                </xsd:appinfo>
            </xsd:annotation>
        </xsd:element>
        <xsd:element name="parameters" type="tns:ArrayOfGenericFaultParameter" minOccurs="0">
            <xsd:annotation>
                <xsd:documentation>Message parameters.</xsd:documentation>
                <xsd:appinfo xmlns:inf="{$this->_baseUrl}/soap/{$this->_storeCode}?services%3DtestModule5AllSoapAndRestV1%2CtestModule5AllSoapAndRestV2">
                    <inf:natureOfType>array</inf:natureOfType>
                </xsd:appinfo>
            </xsd:annotation>
        </xsd:element>
    </xsd:sequence>
</xsd:complexType>
WRAPPED_ERROR_COMPLEX_TYPE;
        $this->assertContains(
            $this->_convertXmlToString($detailsWrappedErrorType),
            $wsdlContent,
            'Details wrapped error complex types declaration is invalid.'
        );

        $detailsParametersType = <<< PARAMETERS_COMPLEX_TYPE
<xsd:complexType name="ArrayOfGenericFaultParameter">
    <xsd:annotation>
        <xsd:documentation>An array of GenericFaultParameter items.</xsd:documentation>
        <xsd:appinfo xmlns:inf="{$this->_soapUrl}"/>
    </xsd:annotation>
    <xsd:sequence>
        <xsd:element name="item" minOccurs="0" maxOccurs="unbounded" type="tns:GenericFaultParameter">
            <xsd:annotation>
                <xsd:documentation>An item of ArrayOfGenericFaultParameter.</xsd:documentation>
                <xsd:appinfo xmlns:inf="{$this->_soapUrl}"/>
            </xsd:annotation>
        </xsd:element>
    </xsd:sequence>
</xsd:complexType>
PARAMETERS_COMPLEX_TYPE;
        // @codingStandardsIgnoreEnd
        $this->assertContains(
            $this->_convertXmlToString($detailsParametersType),
            $wsdlContent,
            'Details parameters (array of parameters) complex types declaration is invalid.'
        );

        $detailsWrappedErrorsType = <<< WRAPPED_ERRORS_COMPLEX_TYPE
<xsd:complexType name="ArrayOfWrappedError">
    <xsd:annotation>
        <xsd:documentation>An array of WrappedError items.</xsd:documentation>
        <xsd:appinfo xmlns:inf="{$this->_baseUrl}/soap/{$this->_storeCode}?services%3DtestModule5AllSoapAndRestV1%2CtestModule5AllSoapAndRestV2"/>
    </xsd:annotation>
    <xsd:sequence>
        <xsd:element name="item" minOccurs="0" maxOccurs="unbounded" type="tns:WrappedError">
            <xsd:annotation>
                <xsd:documentation>An item of ArrayOfWrappedError.</xsd:documentation>
                <xsd:appinfo xmlns:inf="{$this->_baseUrl}/soap/{$this->_storeCode}?services%3DtestModule5AllSoapAndRestV1%2CtestModule5AllSoapAndRestV2"/>
            </xsd:annotation>
        </xsd:element>
    </xsd:sequence>
</xsd:complexType>
WRAPPED_ERRORS_COMPLEX_TYPE;
        // @codingStandardsIgnoreEnd
        $this->assertContains(
            $this->_convertXmlToString($detailsWrappedErrorsType),
            $wsdlContent,
            'Details wrapped errors (array of wrapped errors) complex types declaration is invalid.'
        );
    }
}
