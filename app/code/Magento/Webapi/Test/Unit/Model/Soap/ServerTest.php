<?php
/**
 * Test SOAP server model.
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Webapi\Test\Unit\Model\Soap;

class ServerTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Webapi\Model\Soap\Server */
    protected $_soapServer;

    /** @var \Magento\Store\Model\Store */
    protected $_storeMock;

    /** @var \Magento\Framework\Webapi\Request */
    protected $_requestMock;

    /** @var \Magento\Store\Model\StoreManagerInterface */
    protected $_storeManagerMock;

    /** @var \Magento\Webapi\Model\Soap\ServerFactory */
    protected $_soapServerFactory;

    /** @var \Magento\Framework\Reflection\TypeProcessor|\PHPUnit_Framework_MockObject_MockObject */
    protected $_typeProcessor;

    /** @var \Magento\Webapi\Model\Soap\Wsdl\Generator|\PHPUnit_Framework_MockObject_MockObject */
    protected $wsdlGenerator;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $_scopeConfig;

    protected function setUp()
    {
        $this->_storeManagerMock = $this->getMockBuilder(
            'Magento\Store\Model\StoreManager'
        )->disableOriginalConstructor()->getMock();

        $this->_storeMock = $this->getMockBuilder(
            'Magento\Store\Model\Store'
        )->disableOriginalConstructor()->getMock();
        $this->_storeMock->expects(
            $this->any()
        )->method(
            'getBaseUrl'
        )->will(
            $this->returnValue('http://magento.com/')
        );
        $this->_storeMock->expects($this->any())->method('getCode')->will($this->returnValue('storeCode'));

        $this->_storeManagerMock->expects(
            $this->any()
        )->method(
            'getStore'
        )->will(
            $this->returnValue($this->_storeMock)
        );

        $areaListMock = $this->getMock('Magento\Framework\App\AreaList', [], [], '', false);
        $configScopeMock = $this->getMock('Magento\Framework\Config\ScopeInterface');
        $areaListMock->expects($this->any())->method('getFrontName')->will($this->returnValue('soap'));

        $this->_requestMock = $this->getMockBuilder(
            'Magento\Framework\Webapi\Request'
        )->disableOriginalConstructor()->getMock();

        $this->_soapServerFactory = $this->getMockBuilder(
            'Magento\Webapi\Model\Soap\ServerFactory'
        )->disableOriginalConstructor()->getMock();

        $this->_typeProcessor = $this->getMock(
            'Magento\Framework\Reflection\TypeProcessor',
            [],
            [],
            '',
            false
        );
        $this->wsdlGenerator = $this->getMock('Magento\Webapi\Model\Soap\Wsdl\Generator', [], [], '', false);
        $this->_scopeConfig = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface');

        /** Init SUT. */
        $this->_soapServer = new \Magento\Webapi\Model\Soap\Server(
            $areaListMock,
            $configScopeMock,
            $this->_requestMock,
            $this->_storeManagerMock,
            $this->_soapServerFactory,
            $this->_typeProcessor,
            $this->_scopeConfig,
            $this->wsdlGenerator
        );

        parent::setUp();
    }

    protected function tearDown()
    {
        unset($this->_soapServer);
        unset($this->_requestMock);
        unset($this->_domDocumentFactory);
        unset($this->_storeMock);
        unset($this->_storeManagerMock);
        unset($this->_soapServerFactory);
        parent::tearDown();
    }

    /**
     * Test getApiCharset method.
     */
    public function testGetApiCharset()
    {
        $this->_scopeConfig->expects($this->once())->method('getValue')->will($this->returnValue('Windows-1251'));
        $this->assertEquals(
            'Windows-1251',
            $this->_soapServer->getApiCharset(),
            'API charset encoding getting is invalid.'
        );
    }

    /**
     * Test getApiCharset method with default encoding.
     */
    public function testGetApiCharsetDefaultEncoding()
    {
        $this->_scopeConfig->expects($this->once())->method('getValue')->will($this->returnValue(null));
        $this->assertEquals(
            \Magento\Webapi\Model\Soap\Server::SOAP_DEFAULT_ENCODING,
            $this->_soapServer->getApiCharset(),
            'Default API charset encoding getting is invalid.'
        );
    }

    /**
     * Test getEndpointUri method.
     */
    public function testGetEndpointUri()
    {
        $expectedResult = 'http://magento.com/soap/storeCode';
        $actualResult = $this->_soapServer->getEndpointUri();
        $this->assertEquals($expectedResult, $actualResult, 'Endpoint URI building is invalid.');
    }

    /**
     * Test generate uri with wsdl param as true
     */
    public function testGenerateUriWithWsdlParam()
    {
        $param = "testModule1AllSoapAndRest:V1,testModule2AllSoapNoRest:V1";
        $serviceKey = \Magento\Webapi\Model\Soap\Server::REQUEST_PARAM_SERVICES;
        $this->_requestMock->expects($this->any())->method('getParam')->will($this->returnValue($param));
        $expectedResult = "http://magento.com/soap/storeCode?{$serviceKey}={$param}&wsdl=1";
        $actualResult = $this->_soapServer->generateUri(true);
        $this->assertEquals($expectedResult, urldecode($actualResult), 'URI (with WSDL param) generated is invalid.');
    }

    /**
     * Test generate uri with wsdl param as true
     */
    public function testGenerateUriWithNoWsdlParam()
    {
        $param = "testModule1AllSoapAndRest:V1,testModule2AllSoapNoRest:V1";
        $serviceKey = \Magento\Webapi\Model\Soap\Server::REQUEST_PARAM_SERVICES;
        $this->_requestMock->expects($this->any())->method('getParam')->will($this->returnValue($param));
        $expectedResult = "http://magento.com/soap/storeCode?{$serviceKey}={$param}";
        $actualResult = $this->_soapServer->generateUri(false);
        $this->assertEquals(
            $expectedResult,
            urldecode($actualResult),
            'URI (without WSDL param) generated is invalid.'
        );
    }
}
