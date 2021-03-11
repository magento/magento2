<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Image\Test\Unit\Adapter;

use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class ImageMagickTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject |\Magento\Framework\Filesystem
     */
    protected $filesystemMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject |\Psr\Log\LoggerInterface
     */
    protected $loggerMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject | \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $writeMock;

    /**
     * @var \Magento\Framework\Image\Adapter\ImageMagick
     */
    protected $imageMagic;

    protected function setup(): void
    {
        $objectManager = new ObjectManager($this);
        $this->loggerMock = $this->getMockBuilder(\Psr\Log\LoggerInterface::class)->getMock();
        $this->writeMock = $this->getMockBuilder(
            \Magento\Framework\Filesystem\Directory\WriteInterface::class
        )->getMock();
        $this->filesystemMock = $this->createPartialMock(\Magento\Framework\Filesystem::class, ['getDirectoryWrite']);
        $this->filesystemMock
            ->expects($this->once())
            ->method('getDirectoryWrite')
            ->willReturn($this->writeMock);

        $this->imageMagic = $objectManager
            ->getObject(
                \Magento\Framework\Image\Adapter\ImageMagick::class,
                ['filesystem' => $this->filesystemMock,
                    'logger' => $this->loggerMock]
            );
    }

    /**
     * @param string $imagePath
     * @param string $expectedMessage
     * @dataProvider watermarkDataProvider
     */
    public function testWatermark($imagePath, $expectedMessage)
    {
        $this->expectException('LogicException');
        $this->expectExceptionMessage($expectedMessage);
        $this->imageMagic->watermark($imagePath);
    }

    /**
     * @return array
     */
    public function watermarkDataProvider()
    {
        return [
            ['', \Magento\Framework\Image\Adapter\ImageMagick::ERROR_WATERMARK_IMAGE_ABSENT],
            [__DIR__ . '/not_exists', \Magento\Framework\Image\Adapter\ImageMagick::ERROR_WATERMARK_IMAGE_ABSENT],
            [
                __DIR__ . '/_files/invalid_image.jpg',
                \Magento\Framework\Image\Adapter\ImageMagick::ERROR_WRONG_IMAGE
            ]
        ];
    }

    /**
     */
    public function testSaveWithException()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Unable to write file into directory product/cache. Access forbidden.');

        $exception = new FileSystemException(
            new \Magento\Framework\Phrase('Unable to write file into directory product/cache. Access forbidden.')
        );
        $this->writeMock->method('create')->will($this->throwException($exception));
        $this->loggerMock->expects($this->once())->method('critical')->with($exception);
        $this->imageMagic->save('product/cache', 'sample.jpg');
    }

    /**
     */
    public function testOpenInvalidUrl()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->imageMagic->open('bar://foo.bar');
    }
}
