<?php
/**
 * Tests for \Magento\Webapi\Model\Soap\Wsdl\Generator.
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Webapi\Test\Unit\Model\Soap\Wsdl;

class GeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**  @var \Magento\Webapi\Model\Soap\Wsdl\Generator */
    protected $_wsdlGenerator;

    /**
     * @var \Magento\Framework\Webapi\CustomAttributeTypeLocatorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customAttributeTypeLocator = null;

    /**  @var \Magento\Webapi\Model\Soap\Config|\PHPUnit_Framework_MockObject_MockObject */
    protected $_soapConfigMock;

    /**  @var \Magento\Webapi\Model\Soap\WsdlFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $_wsdlFactoryMock;

    /** @var \Magento\Framework\App\Cache\Type\Webapi|\PHPUnit_Framework_MockObject_MockObject */
    protected $_cacheMock;

    /** @var \Magento\Framework\Reflection\TypeProcessor|\PHPUnit_Framework_MockObject_MockObject */
    protected $_typeProcessor;

    /** @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $storeManagerMock;

    protected function setUp()
    {
        $this->_soapConfigMock = $this->getMockBuilder(
            'Magento\Webapi\Model\Soap\Config'
        )->disableOriginalConstructor()->getMock();

        $_wsdlMock = $this->getMockBuilder(
            'Magento\Webapi\Model\Soap\Wsdl'
        )->disableOriginalConstructor()->setMethods(
            [
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
                'toXML',
            ]
        )->getMock();
        $this->_wsdlFactoryMock = $this->getMockBuilder(
            'Magento\Webapi\Model\Soap\WsdlFactory'
        )->setMethods(
            ['create']
        )->disableOriginalConstructor()->getMock();
        $this->_wsdlFactoryMock->expects($this->any())->method('create')->will($this->returnValue($_wsdlMock));

        $this->_cacheMock = $this->getMockBuilder(
            'Magento\Framework\App\Cache\Type\Webapi'
        )->disableOriginalConstructor()->getMock();
        $this->_cacheMock->expects($this->any())->method('load')->will($this->returnValue(false));
        $this->_cacheMock->expects($this->any())->method('save')->will($this->returnValue(true));

        $this->_typeProcessor = $this->getMock(
            'Magento\Framework\Reflection\TypeProcessor',
            [],
            [],
            '',
            false
        );

        $this->storeManagerMock = $this->getMockBuilder(
            'Magento\Store\Model\StoreManagerInterface'
        )->setMethods(['getStore'])->disableOriginalConstructor()->getMockForAbstractClass();

        $storeMock = $this->getMockBuilder(
            'Magento\Store\Model\Store'
        )->setMethods(['getCode', '__wakeup'])->disableOriginalConstructor()->getMock();

        $this->storeManagerMock->expects($this->any())
            ->method('getStore')
            ->will($this->returnValue($storeMock));

        $storeMock->expects($this->any())
            ->method('getCode')
            ->will($this->returnValue('store_code'));

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->customAttributeTypeLocator = $objectManager
            ->getObject('Magento\Eav\Model\EavCustomAttributeTypeLocator');

        $this->_wsdlGenerator = $objectManager->getObject(
            'Magento\Webapi\Model\Soap\Wsdl\Generator',
            [
                'apiConfig' => $this->_soapConfigMock,
                'wsdlFactory' => $this->_wsdlFactoryMock,
                'cache' => $this->_cacheMock,
                'typeProcessor' => $this->_typeProcessor,
                'storeManagerMock' => $this->storeManagerMock,
                'customAttributeTypeLocator' => $this->customAttributeTypeLocator
            ]
        );

        parent::setUp();
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
     * @expectedException        \Magento\Framework\Webapi\Exception
     * @expectedExceptionMessage exception message
     */
    public function testHandleWithException()
    {
        $genWSDL = 'generatedWSDL';
        $exceptionMsg = 'exception message';
        $requestedService = ['catalogProduct'];

        $wsdlGeneratorMock = $this->getMockBuilder(
            'Magento\Webapi\Model\Soap\Wsdl\Generator'
        )->setMethods(
            ['_collectCallInfo']
        )->setConstructorArgs(
            [
                $this->_soapConfigMock,
                $this->_wsdlFactoryMock,
                $this->_cacheMock,
                $this->_typeProcessor,
                $this->storeManagerMock,
                $this->customAttributeTypeLocator
            ]
        )->getMock();

        $wsdlGeneratorMock->expects($this->once())
            ->method('_collectCallInfo')
            ->willThrowException(new \Magento\Framework\Webapi\Exception(__($exceptionMsg)));

        $this->assertEquals($genWSDL, $wsdlGeneratorMock->generate($requestedService, 'http://magento.host'));
    }
}
