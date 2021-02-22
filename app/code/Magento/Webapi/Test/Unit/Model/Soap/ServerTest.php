<?php
/**
 * Test SOAP server model.
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Webapi\Test\Unit\Model\Soap;

class ServerTest extends \PHPUnit\Framework\TestCase
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

    /** @var \Magento\Framework\Reflection\TypeProcessor|\PHPUnit\Framework\MockObject\MockObject */
    protected $_typeProcessor;

    /** @var \Magento\Webapi\Model\Soap\Wsdl\Generator|\PHPUnit\Framework\MockObject\MockObject */
    protected $wsdlGenerator;

    /** @var \PHPUnit\Framework\MockObject\MockObject */
    protected $_scopeConfig;

    protected function setUp(): void
    {
        $this->_storeManagerMock = $this->getMockBuilder(
            \Magento\Store\Model\StoreManager::class
        )->disableOriginalConstructor()->getMock();

        $this->_storeMock = $this->getMockBuilder(
            \Magento\Store\Model\Store::class
        )->disableOriginalConstructor()->getMock();
        $this->_storeMock->expects(
            $this->any()
        )->method(
            'getBaseUrl'
        )->willReturn(
            'http://magento.com/'
        );
        $this->_storeMock->expects($this->any())->method('getCode')->willReturn('storeCode');

        $this->_storeManagerMock->expects(
            $this->any()
        )->method(
            'getStore'
        )->willReturn(
            $this->_storeMock
        );

        $areaListMock = $this->createMock(\Magento\Framework\App\AreaList::class);
        $configScopeMock = $this->createMock(\Magento\Framework\Config\ScopeInterface::class);
        $areaListMock->expects($this->any())->method('getFrontName')->willReturn('soap');

        $this->_requestMock = $this->getMockBuilder(
            \Magento\Framework\Webapi\Request::class
        )->disableOriginalConstructor()->getMock();

        $this->_soapServerFactory = $this->getMockBuilder(
            \Magento\Webapi\Model\Soap\ServerFactory::class
        )->disableOriginalConstructor()->getMock();

        $this->_typeProcessor = $this->createMock(\Magento\Framework\Reflection\TypeProcessor::class);
        $this->wsdlGenerator = $this->createMock(\Magento\Webapi\Model\Soap\Wsdl\Generator::class);
        $this->_scopeConfig = $this->createMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);

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

    protected function tearDown(): void
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
        $this->_scopeConfig->expects($this->once())->method('getValue')->willReturn('Windows-1251');
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
        $this->_scopeConfig->expects($this->once())->method('getValue')->willReturn(null);
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
        $this->_requestMock->expects($this->any())->method('getParam')->willReturn($param);
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
        $this->_requestMock->expects($this->any())->method('getParam')->willReturn($param);
        $expectedResult = "http://magento.com/soap/storeCode?{$serviceKey}={$param}";
        $actualResult = $this->_soapServer->generateUri(false);
        $this->assertEquals(
            $expectedResult,
            urldecode($actualResult),
            'URI (without WSDL param) generated is invalid.'
        );
    }
}
