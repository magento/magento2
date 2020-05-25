<?php
/**
 * Test SOAP controller class.
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Webapi\Test\Unit\Controller;

use Laminas\Http\Headers;
use Magento\Framework\App\AreaInterface;
use Magento\Framework\App\AreaList;
use Magento\Framework\App\Config;
use Magento\Framework\App\State;
use Magento\Framework\Locale\Resolver;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Webapi\ErrorProcessor;
use Magento\Framework\Webapi\Exception;
use Magento\Framework\Webapi\Request;
use Magento\Framework\Webapi\Response;
use Magento\Framework\Webapi\Rest\Response\RendererFactory;
use Magento\Webapi\Controller\PathProcessor;
use Magento\Webapi\Controller\Soap;
use Magento\Webapi\Model\Soap\Server;
use Magento\Webapi\Model\Soap\Wsdl\Generator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SoapTest extends TestCase
{
    /**
     * @var Soap
     */
    protected $_soapController;

    /**
     * @var Server
     */
    protected $_soapServerMock;

    /**
     * @var Generator
     */
    protected $_wsdlGeneratorMock;

    /**
     * @var Request
     */
    protected $_requestMock;

    /**
     * @var Response
     */
    protected $_responseMock;

    /**
     * @var ErrorProcessor
     */
    protected $_errorProcessorMock;

    /**
     * @var MockObject|ResolverInterface
     */
    protected $_localeMock;

    /**
     * @var MockObject|State
     */
    protected $_appStateMock;

    protected $_appconfig;

    /**
     * Set up Controller object.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $objectManagerHelper = new ObjectManager($this);

        $this->_soapServerMock = $this->getMockBuilder(Server::class)
            ->disableOriginalConstructor()
            ->setMethods(['getApiCharset', 'generateUri', 'handle', 'setWSDL', 'setEncoding', 'setReturnResponse'])
            ->getMock();
        $this->_wsdlGeneratorMock = $this->getMockBuilder(Generator::class)
            ->disableOriginalConstructor()
            ->setMethods(['generate'])
            ->getMock();
        $this->_requestMock = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->setMethods(['getParams', 'getParam', 'getRequestedServices', 'getHttpHost'])
            ->getMock();
        $this->_requestMock->expects($this->any())
            ->method('getHttpHost')
            ->willReturn('testHostName.com');
        $this->_responseMock = $this->getMockBuilder(Response::class)
            ->disableOriginalConstructor()
            ->setMethods(['clearHeaders', 'setHeader', 'sendResponse', 'getHeaders'])
            ->getMock();
        $this->_errorProcessorMock = $this->getMockBuilder(ErrorProcessor::class)
            ->disableOriginalConstructor()
            ->setMethods(['maskException'])
            ->getMock();

        $this->_appStateMock =  $this->createMock(State::class);

        $localeResolverMock = $this->getMockBuilder(
            Resolver::class
        )->disableOriginalConstructor()
            ->setMethods(
                ['getLocale']
            )->getMock();
        $localeResolverMock->expects($this->any())->method('getLocale')->willReturn('en');

        $this->_responseMock->expects($this->any())->method('clearHeaders')->willReturnSelf();
        $this->_responseMock
            ->expects($this->any())
            ->method('getHeaders')
            ->willReturn(new Headers());

        $appconfig = $this->createMock(Config::class);
        $objectManagerHelper->setBackwardCompatibleProperty(
            $this->_requestMock,
            'appConfig',
            $appconfig
        );

        $this->_soapServerMock->expects($this->any())->method('setWSDL')->willReturnSelf();
        $this->_soapServerMock->expects($this->any())->method('setEncoding')->willReturnSelf();
        $this->_soapServerMock->expects($this->any())->method('setReturnResponse')->willReturnSelf();
        $pathProcessorMock = $this->createMock(PathProcessor::class);
        $areaListMock = $this->createMock(AreaList::class);
        $areaMock = $this->getMockForAbstractClass(AreaInterface::class);
        $areaListMock->expects($this->any())->method('getArea')->willReturn($areaMock);

        $rendererMock = $this->getMockBuilder(RendererFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->_soapController = new Soap(
            $this->_requestMock,
            $this->_responseMock,
            $this->_wsdlGeneratorMock,
            $this->_soapServerMock,
            $this->_errorProcessorMock,
            $this->_appStateMock,
            $localeResolverMock,
            $pathProcessorMock,
            $rendererMock,
            $areaListMock
        );
    }

    /**
     * Test successful WSDL content generation.
     */
    public function testDispatchWsdl()
    {
        $params = [
            Server::REQUEST_PARAM_WSDL => 1,
            Request::REQUEST_PARAM_SERVICES => 'foo',
        ];
        $this->_mockGetParam(Server::REQUEST_PARAM_WSDL, 1);
        $this->_requestMock->expects($this->once())
            ->method('getParams')
            ->willReturn($params);
        $wsdl = 'Some WSDL content';
        $this->_wsdlGeneratorMock->expects($this->any())->method('generate')->willReturn($wsdl);

        $this->_soapController->dispatch($this->_requestMock);
        $this->assertEquals($wsdl, $this->_responseMock->getBody());
    }

    public function testDispatchInvalidWsdlRequest()
    {
        $params = [
            Server::REQUEST_PARAM_WSDL => 1,
            'param_1' => 'foo',
            'param_2' => 'bar,'
        ];
        $this->_mockGetParam(Server::REQUEST_PARAM_WSDL, 1);
        $this->_requestMock->expects($this->once())
            ->method('getParams')
            ->willReturn($params);
        $this->_errorProcessorMock->expects(
            $this->any()
        )->method(
            'maskException'
        )->willReturn(
            new Exception(__('message'))
        );
        $wsdl = 'Some WSDL content';
        $this->_wsdlGeneratorMock->expects($this->any())->method('generate')->willReturn($wsdl);
        $encoding = "utf-8";
        $this->_soapServerMock->expects($this->any())->method('getApiCharset')->willReturn($encoding);
        $this->_soapController->dispatch($this->_requestMock);

        $expectedMessage = <<<EXPECTED_MESSAGE
<?xml version="1.0" encoding="{$encoding}"?>
<env:Envelope xmlns:env="http://www.w3.org/2003/05/soap-envelope" >
   <env:Body>
      <env:Fault>
         <env:Code>
            <env:Value>env:Sender</env:Value>
         </env:Code>
         <env:Reason>
            <env:Text xml:lang="en">message</env:Text>
         </env:Reason>
      </env:Fault>
   </env:Body>
</env:Envelope>
EXPECTED_MESSAGE;
        $this->assertXmlStringEqualsXmlString($expectedMessage, $this->_responseMock->getBody());
    }

    /**
     * Test successful SOAP action request dispatch.
     */
    public function testDispatchSoapRequest()
    {
        $this->_soapServerMock->expects($this->once())->method('handle');
        $response = $this->_soapController->dispatch($this->_requestMock);
        $this->assertEquals(200, $response->getHttpResponseCode());
    }

    /**
     * Test handling exception during dispatch.
     */
    public function testDispatchWithException()
    {
        $exceptionMessage = 'some error message';
        $exception = new Exception(__($exceptionMessage));
        $this->_soapServerMock->expects($this->any())->method('handle')->will($this->throwException($exception));
        $this->_errorProcessorMock->expects(
            $this->any()
        )->method(
            'maskException'
        )->willReturn(
            $exception
        );
        $encoding = "utf-8";
        $this->_soapServerMock->expects($this->any())->method('getApiCharset')->willReturn($encoding);

        $this->_soapController->dispatch($this->_requestMock);

        $expectedMessage = <<<EXPECTED_MESSAGE
<?xml version="1.0" encoding="{$encoding}"?>
<env:Envelope xmlns:env="http://www.w3.org/2003/05/soap-envelope" >
   <env:Body>
      <env:Fault>
         <env:Code>
            <env:Value>env:Sender</env:Value>
         </env:Code>
         <env:Reason>
            <env:Text xml:lang="en">some error message</env:Text>
         </env:Reason>
      </env:Fault>
   </env:Body>
</env:Envelope>
EXPECTED_MESSAGE;
        $this->assertXmlStringEqualsXmlString($expectedMessage, $this->_responseMock->getBody());
    }

    /**
     * Mock getParam() of request object to return given value.
     *
     * @param $param
     * @param $value
     */
    protected function _mockGetParam($param, $value)
    {
        $this->_requestMock->expects(
            $this->any()
        )->method(
            'getParam'
        )->with(
            $param
        )->willReturn(
            $value
        );
    }
}
