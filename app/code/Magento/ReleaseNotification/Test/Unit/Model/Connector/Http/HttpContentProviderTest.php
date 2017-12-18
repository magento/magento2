<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ReleaseNotification\Test\Unit\Model\Connector\Http;

use Magento\ReleaseNotification\Model\Connector\Http\ResponseResolver;
use Magento\ReleaseNotification\Model\Connector\Http\HttpContentProvider;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\HTTP\ClientInterface;
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
     * @var ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

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
     * @var ResponseResolver|\PHPUnit_Framework_MockObject_MockObject
     */
    private $responseResolverMock;

    public function setUp()
    {
        $this->loggerMock = $this->getMockBuilder(LoggerInterface::class)
            ->getMockForAbstractClass();
        $this->configMock = $this->getMockBuilder(ScopeConfigInterface::class)
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
                'config' => $this->configMock,
                'productMetadata' => $this->productMetadataMock,
                'session' => $this->sessionMock,
                'responseResolver' => $this->responseResolverMock,
                'logger' => $this->loggerMock
            ]
        );
    }

    /**
     * @param string $metadataVersion
     * @param string $metadataEdition
     * @param string $configUrl
     * @param string $expectedUrl
     * @dataProvider getContentDataProvider
     */
    public function testGetContentSuccess($metadataVersion, $metadataEdition, $configUrl, $expectedUrl)
    {
        $expectedBody = ['test' => 'testValue'];
        $response = json_encode($expectedBody);
        $status = 200;

        $this->configMock->expects($this->any())
            ->method('getValue')
            ->willReturn($configUrl);
        $this->productMetadataMock->expects($this->once())
            ->method('getVersion')
            ->willReturn($metadataVersion);
        $this->productMetadataMock->expects($this->once())
            ->method('getEdition')
            ->willReturn($metadataEdition);
        $this->sessionMock->expects($this->once())
            ->method('getUser')
            ->willReturn($this->sessionMock);
        $this->sessionMock->expects($this->once())
            ->method('getInterfaceLocale')
            ->willReturn('en_US');
        $this->httpClientMock->expects($this->once())
            ->method('get')
            ->with($expectedUrl)
            ->willReturn(null);
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

    /**
     * @param string $metadataVersion
     * @param string $metadataEdition
     * @param string $configUrl
     * @param string $expectedUrl
     * @dataProvider getContentDataProvider
     */
    public function testGetContentFailure($metadataVersion, $metadataEdition, $configUrl, $expectedUrl)
    {
        $this->configMock->expects($this->any())
            ->method('getValue')
            ->willReturn($configUrl);
        $this->productMetadataMock->expects($this->once())
            ->method('getVersion')
            ->willReturn($metadataVersion);
        $this->productMetadataMock->expects($this->once())
            ->method('getEdition')
            ->willReturn($metadataEdition);
        $this->sessionMock->expects($this->once())
            ->method('getUser')
            ->willReturn($this->sessionMock);
        $this->sessionMock->expects($this->once())
            ->method('getInterfaceLocale')
            ->willReturn('en_US');
        $this->httpClientMock->expects($this->once())
            ->method('get')
            ->with($expectedUrl)
            ->will($this->throwException(new \Exception));
        $this->httpClientMock->expects($this->never())->method('getBody');
        $this->httpClientMock->expects($this->never())->method('getStatus');
        $this->responseResolverMock->expects($this->never())->method('getResult');
        $this->loggerMock->expects($this->once())
            ->method('warning');
        $this->loggerMock->expects($this->once())
            ->method('critical');
        $this->assertFalse($this->httpContentProvider->getContent());
    }

    public function getContentDataProvider()
    {
        return [
            [
                '2.2.2-dev',
                'Community',
                'http://api.magento.com/modal',
                'http://api.magento.com/modal?version=2.2.2&edition=Community&locale=en_US'
            ],
            [
                '2.2.2-rc',
                'Community',
                'http://api.magento.com/modal',
                'http://api.magento.com/modal?version=2.2.2&edition=Community&locale=en_US'
            ],
            [
                '2.2.2',
                'Community',
                'http://api.magento.com/modal',
                'http://api.magento.com/modal?version=2.2.2&edition=Community&locale=en_US'
            ],
            [
                '2.3.0',
                'Community',
                'http://api.magento.com/modal',
                'http://api.magento.com/modal?version=2.3.0&edition=Community&locale=en_US'
            ],
        ];
    }
}
