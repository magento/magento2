<?php
/**
 * Test SOAP server model.
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Webapi\Test\Unit\Model\Soap;

use Magento\Framework\App\AreaList;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Config\ScopeInterface;
use Magento\Framework\Reflection\TypeProcessor;
use Magento\Framework\Webapi\Request;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManager;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Webapi\Model\Soap\Server;
use Magento\Webapi\Model\Soap\ServerFactory;
use Magento\Webapi\Model\Soap\Wsdl\Generator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ServerTest extends TestCase
{
    /** @var Server */
    protected $_soapServer;

    /** @var Store */
    protected $_storeMock;

    /** @var Request */
    protected $_requestMock;

    /** @var StoreManagerInterface */
    protected $_storeManagerMock;

    /** @var ServerFactory */
    protected $_soapServerFactory;

    /** @var TypeProcessor|MockObject */
    protected $_typeProcessor;

    /** @var Generator|MockObject */
    protected $wsdlGenerator;

    /** @var MockObject */
    protected $_scopeConfig;

    protected function setUp(): void
    {
        $this->_storeManagerMock = $this->getMockBuilder(
            StoreManager::class
        )->disableOriginalConstructor()
            ->getMock();

        $this->_storeMock = $this->getMockBuilder(
            Store::class
        )->disableOriginalConstructor()
            ->getMock();
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

        $areaListMock = $this->createMock(AreaList::class);
        $configScopeMock = $this->getMockForAbstractClass(ScopeInterface::class);
        $areaListMock->expects($this->any())->method('getFrontName')->willReturn('soap');

        $this->_requestMock = $this->getMockBuilder(
            Request::class
        )->disableOriginalConstructor()
            ->getMock();

        $this->_soapServerFactory = $this->getMockBuilder(
            ServerFactory::class
        )->disableOriginalConstructor()
            ->getMock();

        $this->_typeProcessor = $this->createMock(TypeProcessor::class);
        $this->wsdlGenerator = $this->createMock(Generator::class);
        $this->_scopeConfig = $this->getMockForAbstractClass(ScopeConfigInterface::class);

        /** Init SUT. */
        $this->_soapServer = new Server(
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
            Server::SOAP_DEFAULT_ENCODING,
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
        $serviceKey = Server::REQUEST_PARAM_SERVICES;
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
        $serviceKey = Server::REQUEST_PARAM_SERVICES;
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
