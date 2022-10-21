<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdobeIms\Test\Unit\Model;

use Magento\AdobeIms\Model\GetImage;
use Magento\AdobeImsApi\Api\ConfigInterface;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\HTTP\Client\CurlFactory;
use Magento\Framework\Serialize\Serializer\Json;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Get user image test
 */
class GetImageTest extends TestCase
{
    /**
     * @var CurlFactory|MockObject $curlFactoryMock
     */
    private $curlFactoryMock;

    /**
     * @var GetImage $getImage
     */
    private $getImage;

    /**
     * @var Json|MockObject $jsonMock
     */
    private $jsonMock;

    /**
     * @var LoggerInterface|MockObject $logger
     */
    private $logger;

    /**
     * @var ConfigInterface|MockObject $configInterface
     */
    private $configInterface;

    /**
     * Prepare test objects.
     */
    protected function setUp(): void
    {
        $this->curlFactoryMock = $this->createMock(CurlFactory::class);
        $this->jsonMock = $this->createMock(Json::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->configInterface = $this->createMock(ConfigInterface::class);

        $this->getImage = new GetImage(
            $this->logger,
            $this->curlFactoryMock,
            $this->configInterface,
            $this->jsonMock
        );
    }

    /**
     * Test save.
     *
     * @dataProvider imagesDataProvider
     * @param array $expectedResult
     * @param string $expectedImageUrl
     */
    public function testExecute(array $expectedResult, string $expectedImageUrl): void
    {
        $curl = $this->createMock(Curl::class);
        $this->curlFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($curl);
        $curl->expects($this->exactly(3))
            ->method('addHeader')
            ->willReturn(null);
        $this->configInterface->expects($this->once())
            ->method('getProfileImageUrl')
            ->willReturn('https://adbobe.com/some/image/url');
        $curl->expects($this->once())
            ->method('get')
            ->willReturn(null);
        $this->jsonMock->expects($this->once())
            ->method('unserialize')
            ->willReturn($expectedResult);

        $this->assertEquals($expectedImageUrl, $this->getImage->execute('code'));
    }

    /**
     * Get Image with exception
     */
    public function testGetImageWithException(): void
    {
        $this->curlFactoryMock->expects($this->once())
            ->method('create')
            ->willThrowException(new \Exception());
        $this->logger->expects($this->any())
            ->method('critical')
            ->willReturnSelf();
        $this->getImage->execute('code');
    }

    /**
     * Images data provider.
     *
     * @return array
     */
    public function imagesDataProvider(): array
    {
        return [
            [
                'expected_result' => [
                    'user' => [
                        'images' => [
                            50 => 'https://mir-s3-cdn-cf.behance.net/user/50/61269e393218159.5d8e3b72bcfb9.jpg',
                            100 => 'https://mir-s3-cdn-cf.behance.net/user/100/61269e393218159.5d8e3b72bcfb9.jpg',
                            115 => 'https://mir-s3-cdn-cf.behance.net/user/115/61269e393218159.5d8e3b72bcfb9.jpg',
                            230 => 'https://mir-s3-cdn-cf.behance.net/user/230/61269e393218159.5d8e3b72bcfb9.jpg',
                            138 => 'https://mir-s3-cdn-cf.behance.net/user/138/61269e393218159.5d8e3b72bcfb9.jpg',
                            276 => 'https://mir-s3-cdn-cf.behance.net/user/276/61269e393218159.5d8e3b72bcfb9.jpg',
                        ],
                    ],
                    'http_code' => 200,
                ],
                'expected_image_url' => 'https://mir-s3-cdn-cf.behance.net/user/276/61269e393218159.5d8e3b72bcfb9.jpg'
            ]
        ];
    }
}
