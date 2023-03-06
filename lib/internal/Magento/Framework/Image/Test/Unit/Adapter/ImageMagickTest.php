<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Image\Test\Unit\Adapter;

use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Image\Adapter\ImageMagick;
use Magento\Framework\Phrase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class ImageMagickTest extends TestCase
{
    /**
     * @var MockObject|Filesystem
     */
    protected $filesystemMock;

    /**
     * @var MockObject|LoggerInterface
     */
    protected $loggerMock;

    /**
     * @var MockObject|WriteInterface
     */
    protected $writeMock;

    /**
     * @var ImageMagick
     */
    protected $imageMagic;

    protected function setup(): void
    {
        $objectManager = new ObjectManager($this);
        $this->loggerMock = $this->getMockBuilder(LoggerInterface::class)
            ->getMock();
        $this->writeMock = $this->getMockBuilder(
            WriteInterface::class
        )->getMock();
        $this->filesystemMock = $this->createPartialMock(Filesystem::class, ['getDirectoryWrite']);
        $this->filesystemMock
            ->expects($this->once())
            ->method('getDirectoryWrite')
            ->willReturn($this->writeMock);

        $this->imageMagic = $objectManager
            ->getObject(
                ImageMagick::class,
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
    public function watermarkDataProvider(): array
    {
        return [
            ['', ImageMagick::ERROR_WATERMARK_IMAGE_ABSENT],
            ['not_exist', ImageMagick::ERROR_WATERMARK_IMAGE_ABSENT],
            [
                __DIR__ . '/_files/invalid_image.jpg',
                ImageMagick::ERROR_WRONG_IMAGE
            ]
        ];
    }

    public function testSaveWithException()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('Unable to write file into directory product/cache. Access forbidden.');
        $exception = new FileSystemException(
            new Phrase('Unable to write file into directory product/cache. Access forbidden.')
        );
        $this->writeMock->method('create')->willThrowException($exception);
        $this->loggerMock->expects($this->once())->method('critical')->with($exception);
        $this->imageMagic->save('product/cache', 'sample.jpg');
    }

    /**
     * Test open() with invalid URL.
     */
    public function testOpenInvalidUrl()
    {
        require_once __DIR__ . '/_files/global_php_mock.php';

        $this->expectException(\InvalidArgumentException::class);

        $this->imageMagic->open('bar://foo.bar');
    }
}
