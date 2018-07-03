<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ReleaseNotification\Test\Unit\Model\ContentProvider\Http;

use Magento\ReleaseNotification\Model\ContentProvider\Http\HttpContentProvider;
use Magento\ReleaseNotification\Model\ContentProvider\Http\UrlBuilder;
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
     * @var UrlBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlBuilderMock;

    /**
     * @var ClientInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $httpClientMock;

    public function setUp()
    {
        $this->loggerMock = $this->getMockBuilder(LoggerInterface::class)
            ->getMockForAbstractClass();
        $this->urlBuilderMock = $this->getMockBuilder(UrlBuilder::class)
            ->disableOriginalConstructor()
            ->setMethods(['getUrl'])
            ->getMock();
        $this->httpClientMock = $this->getMockBuilder(ClientInterface::class)
            ->getMockForAbstractClass();

        $objectManager = new ObjectManager($this);
        $this->httpContentProvider = $objectManager->getObject(
            HttpContentProvider::class,
            [
                'httpClient' => $this->httpClientMock,
                'urlBuilder' => $this->urlBuilderMock,
                'logger' => $this->loggerMock
            ]
        );
    }

    public function testGetContentSuccess()
    {
        $version = '2.3.0';
        $edition = 'Community';
        $locale = 'fr_FR';
        $url = 'https://content.url.example/'. $version . '/' . $edition . '/' . $locale . '.json';
        $response = '{"return":"success"}';

        $this->urlBuilderMock->expects($this->any())
            ->method('getUrl')
            ->willReturn($url);
        $this->httpClientMock->expects($this->once())
            ->method('get')
            ->with($url);
        $this->httpClientMock->expects($this->once())
            ->method('getBody')
            ->willReturn($response);
        $this->httpClientMock->expects($this->once())
            ->method('getStatus')
            ->willReturn(200);
        $this->loggerMock->expects($this->never())
            ->method('warning');

        $this->assertEquals($response, $this->httpContentProvider->getContent($version, $edition, $locale));
    }

    public function testGetContentFailure()
    {
        $version = '2.3.5';
        $edition = 'Community';
        $locale = 'fr_FR';
        $url = 'https://content.url.example/'. $version . '/' . $edition . '/' . $locale . '.json';

        $this->urlBuilderMock->expects($this->any())
            ->method('getUrl')
            ->with($version, $edition, $locale)
            ->willReturn($url);
        $this->httpClientMock->expects($this->once())
            ->method('get')
            ->with($url)
            ->will($this->throwException(new \Exception));
        $this->httpClientMock->expects($this->never())->method('getBody');
        $this->loggerMock->expects($this->once())
            ->method('warning');

        $this->assertFalse($this->httpContentProvider->getContent($version, $edition, $locale));
    }

    public function testGetContentSuccessOnLocaleDefault()
    {
        $version = '2.3.1';
        $edition = 'Community';
        $locale = 'fr_FR';
        $urlLocale = 'https://content.url.example/'. $version . '/' . $edition . '/' . $locale . '.json';
        $urlDefaultLocale = 'https://content.url.example/'. $version . '/' . $edition . '/en_US.json';
        $response = '{"return":"default-locale"}';

        $this->urlBuilderMock->expects($this->exactly(2))
            ->method('getUrl')
            ->withConsecutive(
                [$version, $edition, $locale],
                [$version, $edition, 'en_US']
            )
            ->willReturnOnConsecutiveCalls($urlLocale, $urlDefaultLocale);
        $this->httpClientMock->expects($this->exactly(2))
            ->method('get')
            ->withConsecutive([$urlLocale], [$urlDefaultLocale]);
        $this->httpClientMock->expects($this->exactly(2))
            ->method('getBody')
            ->willReturnOnConsecutiveCalls('', $response);
        $this->httpClientMock->expects($this->exactly(2))
            ->method('getStatus')
            ->willReturnOnConsecutiveCalls(404, 200);
        $this->loggerMock->expects($this->never())
            ->method('warning');

        $this->assertEquals($response, $this->httpContentProvider->getContent($version, $edition, $locale));
    }

    /**
     * @param string $version
     * @param string $edition
     * @param string $locale
     * @param string $response
     * @dataProvider getGetContentOnDefaultOrEmptyProvider
     */
    public function testGetContentSuccessOnDefaultOrEmpty($version, $edition, $locale, $response)
    {
        $urlLocale = 'https://content.url.example/'. $version . '/' . $edition . '/' . $locale . '.json';
        $urlDefaultLocale = 'https://content.url.example/'. $version . '/' . $edition . '/en_US.json';
        $urlDefault = 'https://content.url.example/' . $version . '/default.json';

        $this->urlBuilderMock->expects($this->exactly(3))
            ->method('getUrl')
            ->withConsecutive(
                [$version, $edition, $locale],
                [$version, $edition, 'en_US'],
                [$version, '', 'default']
            )
            ->willReturnOnConsecutiveCalls($urlLocale, $urlDefaultLocale, $urlDefault);
        $this->httpClientMock->expects($this->exactly(3))
            ->method('get')
            ->withConsecutive([$urlLocale], [$urlDefaultLocale], [$urlDefault]);
        $this->httpClientMock->expects($this->exactly(3))
            ->method('getBody')
            ->willReturnOnConsecutiveCalls('', '', $response);
        $this->httpClientMock->expects($this->exactly(3))
            ->method('getStatus')
            ->willReturnOnConsecutiveCalls(404, 404, 200);
        $this->loggerMock->expects($this->never())
            ->method('warning');

        $this->assertEquals($response, $this->httpContentProvider->getContent($version, $edition, $locale));
    }

    /**
     * @return array
     */
    public function getGetContentOnDefaultOrEmptyProvider()
    {
        return [
            'default-fr_FR' => [
                '2.3.0',
                'Community',
                'fr_FR',
                '{"return":"default-fr_FR"}'
            ],
            'default-en_US' => [
                '2.3.0',
                'Community',
                'en_US',
                '{"return":"default-en_US"}'
            ],
            'empty-fr_FR' => [
                '2.3.0',
                'Community',
                'fr_FR',
                '{"return":"empty-fr_FR"}'
            ],
            'empty-en_US' => [
                '2.3.0',
                'Community',
                'en_US',
                '{"return":"empty-en_US"}'
            ]
        ];
    }
}
