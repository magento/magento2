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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Webapi\Model\Soap\Wsdl;

class GeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**  @var \Magento\Webapi\Model\Soap\Wsdl\Generator */
    protected $_wsdlGenerator;

    /**  @var \Magento\Webapi\Model\Soap\Config|\PHPUnit_Framework_MockObject_MockObject */
    protected $_soapConfigMock;

    /**  @var \Magento\Webapi\Model\Soap\Wsdl\Factory|\PHPUnit_Framework_MockObject_MockObject */
    protected $_wsdlFactoryMock;

    /** @var \Magento\Webapi\Model\Cache\Type|\PHPUnit_Framework_MockObject_MockObject */
    protected $_cacheMock;

    /** @var \Magento\Webapi\Model\Config\ClassReflector\TypeProcessor|\PHPUnit_Framework_MockObject_MockObject */
    protected $_typeProcessor;

    /** @var \Magento\Framework\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $storeManagerMock;

    protected function setUp()
    {
        $this->_soapConfigMock = $this->getMockBuilder(
            'Magento\Webapi\Model\Soap\Config'
        )->disableOriginalConstructor()->getMock();

        $_wsdlMock = $this->getMockBuilder(
            'Magento\Webapi\Model\Soap\Wsdl'
        )->disableOriginalConstructor()->setMethods(
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
        )->getMock();
        $this->_wsdlFactoryMock = $this->getMockBuilder(
            'Magento\Webapi\Model\Soap\Wsdl\Factory'
        )->setMethods(
            array('create')
        )->disableOriginalConstructor()->getMock();
        $this->_wsdlFactoryMock->expects($this->any())->method('create')->will($this->returnValue($_wsdlMock));

        $this->_cacheMock = $this->getMockBuilder(
            'Magento\Webapi\Model\Cache\Type'
        )->disableOriginalConstructor()->getMock();
        $this->_cacheMock->expects($this->any())->method('load')->will($this->returnValue(false));
        $this->_cacheMock->expects($this->any())->method('save')->will($this->returnValue(true));

        $this->_typeProcessor = $this->getMock(
            'Magento\Webapi\Model\Config\ClassReflector\TypeProcessor',
            array(),
            array(),
            '',
            false
        );

        $this->storeManagerMock = $this->getMockBuilder(
            'Magento\Framework\StoreManagerInterface'
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

        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_wsdlGenerator = $helper->getObject(
            'Magento\Webapi\Model\Soap\Wsdl\Generator',
            [
                'apiConfig' => $this->_soapConfigMock,
                'wsdlFactory' => $this->_wsdlFactoryMock,
                'cache' => $this->_cacheMock,
                'typeProcessor' => $this->_typeProcessor,
                'storeManagerMock' => $this->storeManagerMock
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
     * @expectedException        \Magento\Webapi\Exception
     * @expectedExceptionMessage exception message
     */
    public function testHandleWithException()
    {
        $genWSDL = 'generatedWSDL';
        $exceptionMsg = 'exception message';
        $requestedService = array('catalogProduct');

        $wsdlGeneratorMock = $this->getMockBuilder(
            'Magento\Webapi\Model\Soap\Wsdl\Generator'
        )->setMethods(
            array('_collectCallInfo')
        )->setConstructorArgs(
            [
                $this->_soapConfigMock,
                $this->_wsdlFactoryMock,
                $this->_cacheMock,
                $this->_typeProcessor,
                $this->storeManagerMock
            ]
        )->getMock();

        $wsdlGeneratorMock->expects(
            $this->once()
        )->method(
            '_collectCallInfo'
        )->will(
            $this->throwException(new \Magento\Webapi\Exception($exceptionMsg))
        );

        $this->assertEquals($genWSDL, $wsdlGeneratorMock->generate($requestedService, 'http://magento.host'));
    }
}
