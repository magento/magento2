<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ReleaseNotification\Test\Unit\Model\ContentProvider\Http;

use Magento\ReleaseNotification\Model\ContentProvider\Http\ResponseResolver;
use Magento\ReleaseNotification\Model\ContentProvider\Http\HttpContentProvider;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\HTTP\ClientInterface;
use Magento\ReleaseNotification\Model\ContentProvider\Http\UrlBuilder;
use Psr\Log\LoggerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * A unit test for testing of the representation of a HttpContentProvider request.
 */
class HttpContentProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var HttpContentProvider
     */
    private $httpContentProvider;

    /**
     * @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $loggerMock;

    /**
     * @var UrlBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlBuilderMock;

    /**
     * @var ProductMetadataInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productMetadataMock;

    /**
     * @var Session|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sessionMock;

    /**
     * @var ClientInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $httpClientMock;

    /**
     * @var \Magento\ReleaseNotification\Model\ContentProvider\Http\ResponseResolver|\PHPUnit_Framework_MockObject_MockObject
     */
    private $responseResolverMock;

    public function setUp()
    {
        $this->loggerMock = $this->getMockBuilder(LoggerInterface::class)
            ->getMockForAbstractClass();
        $this->urlBuilderMock = $this->getMockBuilder(UrlBuilder::class)
            ->disableOriginalConstructor()
            ->setMethods(['getUrl'])
            ->getMockForAbstractClass();
        $this->productMetadataMock = $this->getMockBuilder(ProductMetadataInterface::class)
            ->getMockForAbstractClass();
        $this->sessionMock = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->setMethods(['getUser', 'getInterfaceLocale'])
            ->getMock();
        $this->httpClientMock = $this->getMockBuilder(ClientInterface::class)
            ->getMockForAbstractClass();
        $this->responseResolverMock = $this->getMockBuilder(ResponseResolver::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new ObjectManager($this);
        $this->httpContentProvider = $objectManager->getObject(
            HttpContentProvider::class,
            [
                'httpClient' => $this->httpClientMock,
                'urlBuilder' => $this->urlBuilderMock,
                'productMetadata' => $this->productMetadataMock,
                'session' => $this->sessionMock,
                'responseResolver' => $this->responseResolverMock,
                'logger' => $this->loggerMock
            ]
        );
    }

    public function testGetContentSuccess()
    {
        $expectedBody = ['test' => 'testValue'];
        $response = json_encode($expectedBody);
        $status = 200;

        $this->urlBuilderMock->expects($this->any())
            ->method('getUrl')
            ->willReturn('contentUrl');
        $this->productMetadataMock->expects($this->once())
            ->method('getVersion')
            ->willReturn('version');
        $this->productMetadataMock->expects($this->once())
            ->method('getEdition')
            ->willReturn('edition');
        $this->sessionMock->expects($this->once())
            ->method('getUser')
            ->willReturn($this->sessionMock);
        $this->sessionMock->expects($this->once())
            ->method('getInterfaceLocale')
            ->willReturn('en_US');
        $this->httpClientMock->expects($this->once())
            ->method('get')
            ->with('contentUrl');
        $this->httpClientMock->expects($this->once())
            ->method('getBody')
            ->willReturn($response);
        $this->httpClientMock->expects($this->once())
            ->method('getStatus')
            ->willReturn($status);
        $this->responseResolverMock->expects($this->once())
            ->method('getResult')
            ->with($response, $status)
            ->willReturn($expectedBody);
        $this->assertEquals($expectedBody, $this->httpContentProvider->getContent());
    }

    public function testGetContentFailure()
    {
        $this->urlBuilderMock->expects($this->any())
            ->method('getUrl')
            ->with('version', 'edition', 'en_US')
            ->willReturn('contentUrl');
        $this->productMetadataMock->expects($this->once())
            ->method('getVersion')
            ->willReturn('version');
        $this->productMetadataMock->expects($this->once())
            ->method('getEdition')
            ->willReturn('edition');
        $this->sessionMock->expects($this->once())
            ->method('getUser')
            ->willReturn($this->sessionMock);
        $this->sessionMock->expects($this->once())
            ->method('getInterfaceLocale')
            ->willReturn('en_US');
        $this->httpClientMock->expects($this->once())
            ->method('get')
            ->with('contentUrl')
            ->will($this->throwException(new \Exception));
        $this->httpClientMock->expects($this->never())->method('getBody');
        $this->httpClientMock->expects($this->never())->method('getStatus');
        $this->responseResolverMock->expects($this->never())->method('getResult');
        $this->loggerMock->expects($this->once())
            ->method('warning');
        $this->assertFalse($this->httpContentProvider->getContent());
    }

    public function testGetContentSuccessOnEditionDefault()
    {
        $expectedBody = ['test' => 'testValue'];
        $response = json_encode($expectedBody);

        $this->urlBuilderMock->expects($this->any())
            ->method('getUrl')
            ->willReturn('contentUrl');
        $this->productMetadataMock->expects($this->once())
            ->method('getVersion')
            ->willReturn('version');
        $this->productMetadataMock->expects($this->once())
            ->method('getEdition')
            ->willReturn('edition');
        $this->sessionMock->expects($this->once())
            ->method('getUser')
            ->willReturn($this->sessionMock);
        $this->sessionMock->expects($this->once())
            ->method('getInterfaceLocale')
            ->willReturn('fr_FR');
        $this->httpClientMock->expects($this->exactly(2))
            ->method('get')
            ->with('contentUrl');
        $this->httpClientMock->expects($this->at(0))
            ->method('getBody')
            ->willReturn('');
        $this->httpClientMock->expects($this->at(1))
            ->method('getBody')
            ->willReturn($response);
        $this->httpClientMock->expects($this->at(0))
            ->method('getStatus')
            ->willReturn(400);
        $this->httpClientMock->expects($this->at(1))
            ->method('getStatus')
            ->willReturn(200);
        $this->responseResolverMock->expects($this->at(0))
            ->method('getResult')
            ->willReturn(false);
        $this->responseResolverMock->expects($this->at(1))
            ->method('getResult')
            ->willReturn($expectedBody);
        $this->assertEquals($expectedBody, $this->httpContentProvider->getContent());
    }

    public function testGetContentSuccessOnVersionDefault()
    {
        $expectedBody = ['test' => 'testValue'];
        $response = json_encode($expectedBody);

        $this->urlBuilderMock->expects($this->any())
            ->method('getUrl')
            ->willReturn('contentUrl');
        $this->productMetadataMock->expects($this->once())
            ->method('getVersion')
            ->willReturn('version');
        $this->productMetadataMock->expects($this->once())
            ->method('getEdition')
            ->willReturn('edition');
        $this->sessionMock->expects($this->once())
            ->method('getUser')
            ->willReturn($this->sessionMock);
        $this->sessionMock->expects($this->once())
            ->method('getInterfaceLocale')
            ->willReturn('fr_FR');
        $this->httpClientMock->expects($this->exactly(3))
            ->method('get')
            ->with('contentUrl');
        $this->httpClientMock->expects($this->at(0))
            ->method('getBody')
            ->willReturn('');
        $this->httpClientMock->expects($this->at(1))
            ->method('getBody')
            ->willReturn('');
        $this->httpClientMock->expects($this->at(2))
            ->method('getBody')
            ->willReturn($response);
        $this->httpClientMock->expects($this->at(0))
            ->method('getStatus')
            ->willReturn(400);
        $this->httpClientMock->expects($this->at(1))
            ->method('getStatus')
            ->willReturn(400);
        $this->httpClientMock->expects($this->at(2))
            ->method('getStatus')
            ->willReturn(200);
        $this->responseResolverMock->expects($this->at(0))
            ->method('getResult')
            ->willReturn(false);
        $this->responseResolverMock->expects($this->at(1))
            ->method('getResult')
            ->willReturn(false);
        $this->responseResolverMock->expects($this->at(2))
            ->method('getResult')
            ->willReturn($expectedBody);
        $this->assertEquals($expectedBody, $this->httpContentProvider->getContent());
    }

    public function testGetEmptyContent()
    {
        $this->urlBuilderMock->expects($this->any())
            ->method('getUrl')
            ->willReturn('contentUrl');
        $this->productMetadataMock->expects($this->once())
            ->method('getVersion')
            ->willReturn('version');
        $this->productMetadataMock->expects($this->once())
            ->method('getEdition')
            ->willReturn('edition');
        $this->sessionMock->expects($this->once())
            ->method('getUser')
            ->willReturn($this->sessionMock);
        $this->sessionMock->expects($this->once())
            ->method('getInterfaceLocale')
            ->willReturn('fr_FR');
        $this->httpClientMock->expects($this->exactly(3))
            ->method('get')
            ->with('contentUrl');
        $this->httpClientMock->expects($this->exactly(3))
            ->method('getBody')
            ->willReturn('');
        $this->httpClientMock->expects($this->exactly(3))
            ->method('getStatus')
            ->willReturn(400);
        $this->responseResolverMock->expects($this->exactly(3))
            ->method('getResult')
            ->willReturn(false);
        $this->loggerMock->expects($this->never())
            ->method('warning');
        $this->assertFalse($this->httpContentProvider->getContent());
    }
}
