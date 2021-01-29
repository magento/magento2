<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Test\Unit;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as HelperObjectManager;
use PHPUnit\Framework\MockObject\MockObject as MockObject;
use Magento\Framework\App\Request\Http as RequestHttp;
use Magento\Framework\App\Response\Http as ResponseHttp;
use Magento\Framework\App\Http as AppHttp;
use Magento\Framework\App\FrontControllerInterface;
use Magento\Framework\Event\Manager;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\App\AreaList;
use Magento\Framework\App\ObjectManager\ConfigLoader;
use Magento\Framework\App\ExceptionHandlerInterface;
use Magento\Framework\Stdlib\Cookie\CookieReaderInterface;
use Magento\Framework\App\Route\ConfigInterface\Proxy;
use Magento\Framework\App\Request\PathInfoProcessorInterface;
use Magento\Framework\Stdlib\StringUtils;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class HttpTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var HelperObjectManager
     */
    private $objectManager;

    /**
     * @var ResponseHttp|MockObject
     */
    private $responseMock;

    /**
     * @var AppHttp
     */
    private $http;

    /**
     * @var FrontControllerInterface|MockObject
     */
    private $frontControllerMock;

    /**
     * @var Manager|MockObject
     */
    private $eventManagerMock;

    /**
     * @var RequestHttp|MockObject
     */
    private $requestMock;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    private $objectManagerMock;

    /**
     * @var AreaList|MockObject
     */
    private $areaListMock;

    /**
     * @var ConfigLoader|MockObject
     */
    private $configLoaderMock;

    /**
     * @var ExceptionHandlerInterface|MockObject
     */
    private $exceptionHandlerMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = new HelperObjectManager($this);
        $cookieReaderMock = $this->getMockBuilder(CookieReaderInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $routeConfigMock = $this->getMockBuilder(Proxy::class)
            ->disableOriginalConstructor()
            ->getMock();
        $pathInfoProcessorMock = $this->getMockBuilder(PathInfoProcessorInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $converterMock = $this->getMockBuilder(StringUtils::class)
            ->disableOriginalConstructor()
            ->setMethods(['cleanString'])
            ->getMock();
        $objectManagerMock = $this->getMockBuilder(ObjectManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->requestMock = $this->getMockBuilder(RequestHttp::class)
            ->setConstructorArgs(
                [
                    'cookieReader' => $cookieReaderMock,
                    'converter' => $converterMock,
                    'routeConfig' => $routeConfigMock,
                    'pathInfoProcessor' => $pathInfoProcessorMock,
                    'objectManager' => $objectManagerMock
                ]
            )
            ->setMethods(['getFrontName', 'isHead'])
            ->getMock();
        $this->areaListMock = $this->getMockBuilder(AreaList::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCodeByFrontName'])
            ->getMock();
        $this->configLoaderMock = $this->getMockBuilder(\Magento\Framework\App\ObjectManager\ConfigLoader::class)
            ->disableOriginalConstructor()
            ->setMethods(['load'])
            ->getMock();
        $this->objectManagerMock = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $this->responseMock = $this->createMock(ResponseHttp::class);
        $this->frontControllerMock = $this->getMockBuilder(\Magento\Framework\App\FrontControllerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['dispatch'])
            ->getMock();
        $this->eventManagerMock = $this->getMockBuilder(Manager::class)
            ->disableOriginalConstructor()
            ->setMethods(['dispatch'])
            ->getMock();
        $this->exceptionHandlerMock = $this->getMockForAbstractClass(ExceptionHandlerInterface::class);

        $this->http = $this->objectManager->getObject(
            AppHttp::class,
            [
                'objectManager' => $this->objectManagerMock,
                'eventManager' => $this->eventManagerMock,
                'areaList' => $this->areaListMock,
                'request' => $this->requestMock,
                'response' => $this->responseMock,
                'configLoader' => $this->configLoaderMock,
                'exceptionHandler' => $this->exceptionHandlerMock,
            ]
        );
    }

    /**
     * Asserts mock objects with methods that are expected to be called when http->launch() is invoked.
     */
    private function setUpLaunch()
    {
        $frontName = 'frontName';
        $areaCode = 'areaCode';
        $this->requestMock->expects($this->once())
            ->method('getFrontName')
            ->willReturn($frontName);
        $this->areaListMock->expects($this->once())
            ->method('getCodeByFrontName')
            ->with($frontName)
            ->willReturn($areaCode);
        $this->configLoaderMock->expects($this->once())
            ->method('load')
            ->with($areaCode)
            ->willReturn([]);
        $this->objectManagerMock->expects($this->once())->method('configure')->with([]);
        $this->objectManagerMock->expects($this->once())
            ->method('get')
            ->with(\Magento\Framework\App\FrontControllerInterface::class)
            ->willReturn($this->frontControllerMock);
        $this->frontControllerMock->expects($this->once())
            ->method('dispatch')
            ->with($this->requestMock)
            ->willReturn($this->responseMock);
    }

    public function testLaunchSuccess()
    {
        $this->setUpLaunch();
        $this->requestMock->expects($this->once())
            ->method('isHead')
            ->willReturn(false);
        $this->eventManagerMock->expects($this->once())
            ->method('dispatch')
            ->with(
                'controller_front_send_response_before',
                ['request' => $this->requestMock, 'response' => $this->responseMock]
            );
        $this->assertSame($this->responseMock, $this->http->launch());
    }

    /**
     */
    public function testLaunchException()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Message');

        $this->setUpLaunch();
        $this->frontControllerMock->expects($this->once())
            ->method('dispatch')
            ->with($this->requestMock)
            ->willThrowException(
                new \Exception('Message')
            );
        $this->http->launch();
    }

    /**
     * Test that HEAD requests lead to an empty body and a Content-Length header matching the original body size.
     * @dataProvider dataProviderForTestLaunchHeadRequest
     * @param string $body
     * @param int $expectedLength
     */
    public function testLaunchHeadRequest($body, $expectedLength)
    {
        $this->setUpLaunch();
        $this->requestMock->expects($this->once())
            ->method('isHead')
            ->willReturn(true);
        $this->responseMock->expects($this->once())
            ->method('getHttpResponseCode')
            ->willReturn(200);
        $this->responseMock->expects($this->once())
            ->method('getContent')
            ->willReturn($body);
        $this->responseMock->expects($this->once())
            ->method('clearBody')
            ->willReturn($this->responseMock);
        $this->responseMock->expects($this->once())
            ->method('setHeader')
            ->with('Content-Length', $expectedLength)
            ->willReturn($this->responseMock);
        $this->eventManagerMock->expects($this->once())
            ->method('dispatch')
            ->with(
                'controller_front_send_response_before',
                ['request' => $this->requestMock, 'response' => $this->responseMock]
            );
        $this->assertSame($this->responseMock, $this->http->launch());
    }

    /**
     * Different test content for responseMock with their expected lengths in bytes.
     * @return array
     */
    public function dataProviderForTestLaunchHeadRequest(): array
    {
        return [
            [
                "<html><head></head><body>Test</body></html>",                // Ascii text
                43                                                            // Expected Content-Length
            ],
            [
                "<html><head></head><body>部落格</body></html>",               // Multi-byte characters
                48                                                            // Expected Content-Length
            ],
            [
                "<html><head></head><body>\0</body></html>",     // Null byte
                40                                                            // Expected Content-Length
            ],
            [
                "<html><head></head>خرید<body></body></html>",                // LTR text
                47                                                            // Expected Content-Length
            ]
        ];
    }
}
