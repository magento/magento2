<?php
/**
 * Tests for \Magento\Webapi\Model\Soap\Wsdl\Generator.
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
namespace Magento\Webapi\Model\Soap\Wsdl;

class GeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**  @var \Magento\Webapi\Model\Soap\Wsdl\Generator */
    protected $_wsdlGenerator;

    /**  @var \Magento\Webapi\Model\Soap\Config */
    protected $_soapConfigMock;

    /**  @var \Magento\Webapi\Model\Soap\Wsdl\Factory */
    protected $_wsdlFactoryMock;

    /** @var \Magento\Webapi\Model\Cache\Type */
    protected $_cacheMock;

    /** @var \Magento\DomDocument\Factory */
    protected $_domDocumentFactory;

    protected function setUp()
    {
        $this->_soapConfigMock = $this->getMockBuilder('Magento\Webapi\Model\Soap\Config')
            ->disableOriginalConstructor()
            ->getMock();

        $_wsdlMock = $this->getMockBuilder('Magento\Webapi\Model\Soap\Wsdl')
            ->disableOriginalConstructor()
            ->setMethods(
                array(
                    'addSchemaTypeSection',
                    'addService',
                    'addPortType',
                    'addBinding',
                    'addSoapBinding',
                    'addElement',
                    'addComplexType',
                    'addMessage',
                    'addPortOperation',
                    'addBindingOperation',
                    'addSoapOperation',
                    'toXML'
                )
            )
            ->getMock();
        $this->_wsdlFactoryMock = $this->getMockBuilder('Magento\Webapi\Model\Soap\Wsdl\Factory')
            ->setMethods(array('create'))
            ->disableOriginalConstructor()
            ->getMock();
        $this->_wsdlFactoryMock->expects($this->any())->method('create')->will($this->returnValue($_wsdlMock));

        $this->_cacheMock = $this->getMockBuilder('Magento\Webapi\Model\Cache\Type')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_cacheMock->expects($this->any())->method('load')->will($this->returnValue(false));
        $this->_cacheMock->expects($this->any())->method('save')->will($this->returnValue(true));

        $this->_domDocumentFactory = $this->getMockBuilder('Magento\DomDocument\Factory')
            ->disableOriginalConstructor()->getMock();

        $this->_wsdlGenerator = new \Magento\Webapi\Model\Soap\Wsdl\Generator(
            $this->_soapConfigMock,
            $this->_wsdlFactoryMock,
            $this->_cacheMock,
            $this->_domDocumentFactory
        );

        parent::setUp();
    }

    protected function tearDown()
    {
        unset($this->_wsdlGenerator);
        unset($this->_soapConfigMock);
        unset($this->_wsdlFactoryMock);
        unset($this->_cacheMock);
        parent::tearDown();
    }

    public function testGetComplexTypeNodes()
    {
        $serviceName = "serviceName";
        $nodesList = $this->_wsdlGenerator->getComplexTypeNodes($serviceName,
            'ItemsResponse',
            $this->_getXsdDocumentWithReferencedTypes()
        );
        $expectedCount = 2;
        $this->assertCount($expectedCount, $nodesList, "Defined complex types count does not match.");
        $actualTypes = array();
        foreach ($nodesList as $node) {
            $actualTypes[] = $node->getAttribute('name');
        }
        $expectedTypes = array($serviceName . 'ItemsResponse', $serviceName . 'ArrayItem');
        $this->assertEquals(
            $expectedCount,
            count(array_intersect($expectedTypes, $actualTypes)),
            "Complex types does not match."
        );
    }

    /**
     * @return \DOMDocument
     */
    protected function _getXsdDocumentWithReferencedTypes()
    {
        $xsd =
            '<xsd:schema xmlns:xsd="http://www.w3.org/2001/XMLSchema" targetNamespace="targetNamespace">
                <xsd:complexType name="ItemRequest">
                    <xsd:sequence>
                        <xsd:element name="id" type="xsd:int" />
                    </xsd:sequence>
                </xsd:complexType>
                <xsd:complexType name="ItemResponse">
                    <xsd:sequence>
                        <xsd:element name="id" type="xsd:int" />
                        <xsd:element name="name"  type="xsd:string" />
                    </xsd:sequence>
                </xsd:complexType>

                <xsd:complexType name="ItemsResponse">
                    <xsd:sequence>
                        <xsd:element minOccurs="0" maxOccurs="unbounded" name="complexObjectArray" type="ArrayItem" />
                        <!-- "item" is required to ensure that the same complex type is included into WSDL only once-->
                        <xsd:element name="item" type="ArrayItem" />
                    </xsd:sequence>
                </xsd:complexType>
                <xsd:complexType name="ArrayItem">
                    <xsd:sequence>
                        <xsd:element name="id" type="xsd:int" />
                        <xsd:element name="name" type="xsd:string" />
                    </xsd:sequence>
                </xsd:complexType>
            </xsd:schema>';
        $xsdDom = new \DOMDocument();
        $xsdDom->loadXML($xsd);
        return $xsdDom;
    }

    /**
     * @return \DOMDocument
     */
    protected function _getXsdDocumentMissingTargetNamespace()
    {
        $xsd =
            '<xsd:schema xmlns:xsd="http://www.w3.org/2001/XMLSchema">
                <xsd:complexType name="ItemRequest">
                    <xsd:sequence>
                        <xsd:element name="id" type="xsd:int" />
                    </xsd:sequence>
                </xsd:complexType>
                <xsd:complexType name="ItemResponse">
                    <xsd:sequence>
                        <xsd:element name="id" type="xsd:int" />
                        <xsd:element name="name"  type="xsd:string" />
                    </xsd:sequence>
                </xsd:complexType>
            </xsd:schema>';
        $xsdDom = new \DOMDocument();
        $xsdDom->loadXML($xsd);
        return $xsdDom;
    }

    /**
     * @dataProvider providerIsComplexType
     */
    public function testIsComplexType($type, $isComplex)
    {
        $this->assertEquals(
            $isComplex,
            $this->_wsdlGenerator->isComplexType($type),
            "Complex type is defined incorrectly"
        );
    }

    public static function providerIsComplexType()
    {
        return array(
            array('xs:int', false),
            array('xsd:string', false),
            array('itemRequest', true),
        );
    }

    /**
     * Test getElementComplexTypeName
     */
    public function testGetElementComplexTypeName()
    {
        $this->assertEquals("Test", $this->_wsdlGenerator->getElementComplexTypeName("test"));
    }

    /**
     * Test getPortTypeName
     */
    public function testGetPortTypeName()
    {
        $this->assertEquals("testPortType", $this->_wsdlGenerator->getPortTypeName("test"));
    }

    /**
     * Test getBindingName
     */
    public function testGetBindingName()
    {
        $this->assertEquals("testBinding", $this->_wsdlGenerator->getBindingName("test"));
    }

    /**
     * Test getPortName
     */
    public function testGetPortName()
    {
        $this->assertEquals("testPort", $this->_wsdlGenerator->getPortName("test"));
    }

    /**
     * test getServiceName
     */
    public function testGetServiceName()
    {
        $this->assertEquals("testService", $this->_wsdlGenerator->getServiceName("test"));
    }

    /**
     * test getOperationName
     */
    public function testGetOperationName()
    {
        $this->assertEquals("resNameMethodName", $this->_wsdlGenerator->getOperationName("resName", "methodName"));
    }

    /**
     * @test
     */
    public function testGetInputMessageName()
    {
        $this->assertEquals("operationNameRequest", $this->_wsdlGenerator->getInputMessageName("operationName"));
    }

    /**
     * @test
     */
    public function testGetOutputMessageName()
    {
        $this->assertEquals("operationNameResponse", $this->_wsdlGenerator->getOutputMessageName("operationName"));
    }

    /**
     * Test exception for handle
     *
     * @expectedException        \Magento\Webapi\Exception
     * @expectedExceptionMessage exception message
     */
    public function testHandleWithException()
    {
        $genWSDL = 'generatedWSDL';
        $exceptionMsg = 'exception message';
        $requestedService = array(
            'catalogProduct' => 'V1',
        );

        $wsdlGeneratorMock = $this->getMockBuilder(
            'Magento\Webapi\Model\Soap\Wsdl\Generator'
        )
            ->setMethods(array('_prepareServiceData'))
            ->setConstructorArgs(
                array(
                    $this->_soapConfigMock,
                    $this->_wsdlFactoryMock,
                    $this->_cacheMock,
                    $this->_domDocumentFactory
                )
            )
            ->getMock();

        $wsdlGeneratorMock->expects($this->once())->method('_prepareServiceData')->will(
            $this->throwException(new \Exception($exceptionMsg))
        );

        $this->assertEquals($genWSDL, $wsdlGeneratorMock->generate($requestedService, 'http://magento.host'));
    }
}
