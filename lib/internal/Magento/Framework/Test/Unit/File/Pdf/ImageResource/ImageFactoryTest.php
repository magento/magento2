<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Test\Unit\File\Pdf\ImageResource;

use Exception;
use Magento\Framework\File\Pdf\ImageResource\ImageFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\Read;
use PHPUnit\Framework\TestCase;
use Zend_Pdf_Resource_Image_Jpeg;
use Zend_Pdf_Resource_Image_Png;

/**
 * Class for testing Magento\Framework\File\Pdf\ImageResource\ImageFactory
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class ImageFactoryTest extends TestCase
{
    /**
     * Url of AWS main image
     */
    private const REMOTE_IMAGE_PATH = 'https://a0.awsstatic.com/libra-css/' .
    'images/logos/aws_smile-header-desktop-en-white_59x35.png';

    /**
     * @var \Magento\Framework\File\Pdf\ImageResource\ImageFactory
     */
    private ImageFactory $factory;

    /**
     * @return void
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Zend_Pdf_Exception
     */
    public function testFactoryWithLocalImage(): void
    {
        $filesystemMock = $this->createMock(Filesystem::class);

        $tempFileResouceFromBucketOrDisk = tmpfile();
        $tempFilenameFromBucketOrDisk = stream_get_meta_data($tempFileResouceFromBucketOrDisk)['uri'];
        $readerMock = $this->createMock(Read::class);
        $readerMock->method('isFile')
            ->with($tempFilenameFromBucketOrDisk)
            ->willReturn(true);
        $imagePath = $this->generateImageByConfig(
            [
                'image-width' => 36,
                'image-height' => 69,
                'image-name' => $tempFilenameFromBucketOrDisk
            ]
        );

        $readerMock->method('readFile')
            ->with($tempFilenameFromBucketOrDisk)
            ->willReturn(file_get_contents($tempFilenameFromBucketOrDisk));

        $filesystemMock->method('getDirectoryRead')
            ->willReturn($readerMock);

        $this->factory = new ImageFactory($filesystemMock);

        /** @var \Zend_Pdf_Resource_Image_Jpeg|\Zend_Pdf_Resource_Image_Png|\Zend_Pdf_Resource_Image_Tiff $result */
        $result = $this->factory->factory($tempFilenameFromBucketOrDisk);
        unlink($imagePath);
        $this->assertEquals(69, $result->getPixelHeight());
        $this->assertEquals(36, $result->getPixelWidth());
        $this->assertInstanceOf(Zend_Pdf_Resource_Image_Jpeg::class, $result);
    }

    /**
     * @param array<mixed> $config
     * @return array|string|string[]|null
     * @throws \Exception
     */
    private function generateImageByConfig(array $config)
    {
        // phpcs:disable Magento2.Functions.DiscouragedFunction
        $binaryData = '';
        $data = str_split(sha1($config['image-name']), 2);
        foreach ($data as $item) {
            $binaryData .= base_convert($item, 16, 2);
        }
        $binaryData = str_split($binaryData, 1);

        $image = imagecreate($config['image-width'], $config['image-height']);
        $bgColor = imagecolorallocate($image, 240, 240, 240);
        // mt_rand() here is not for cryptographic use.
        // phpcs:ignore Magento2.Security.InsecureFunction
        $fgColor = imagecolorallocate($image, mt_rand(0, 230), mt_rand(0, 230), mt_rand(0, 230));
        $colors = [$fgColor, $bgColor];
        imagefilledrectangle($image, 0, 0, $config['image-width'], $config['image-height'], $bgColor);

        for ($row = 10; $row < ($config['image-height'] - 10); $row += 10) {
            for ($col = 10; $col < ($config['image-width'] - 10); $col += 10) {
                if (next($binaryData) === false) {
                    reset($binaryData);
                }
                imagefilledrectangle($image, $row, $col, $row + 10, $col + 10, $colors[current($binaryData)]);
            }
        }

        $imagePath = $config['image-name'];
        $imagePath = preg_replace('|/{2,}|', '/', $imagePath);
        $memory = fopen('php://memory', 'r+');
        if (!imagejpeg($image, $memory)) {
            throw new Exception('Could not create picture ' . $imagePath);
        }
        file_put_contents($imagePath, stream_get_contents($memory, -1, 0));
        fclose($memory);
        imagedestroy($image);
        // phpcs:enable

        return $imagePath;
    }

    /**
     * @return void
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Zend_Pdf_Exception
     */
    public function testFactoryWithRemoteImage(): void
    {
        $filesystemMock = $this->createMock(Filesystem::class);

        $readerMock = $this->createMock(Read::class);
        $readerMock->method('isFile')
            ->with(self::REMOTE_IMAGE_PATH)
            ->willReturn(true);
        $readerMock->method('readFile')
            ->with(self::REMOTE_IMAGE_PATH)
            ->willReturn(file_get_contents(self::REMOTE_IMAGE_PATH));

        $filesystemMock->method('getDirectoryRead')
            ->willReturn($readerMock);

        $this->factory = new ImageFactory($filesystemMock);

        /** @var \Zend_Pdf_Resource_Image_Jpeg|\Zend_Pdf_Resource_Image_Png|\Zend_Pdf_Resource_Image_Tiff $result */
        $result = $this->factory->factory(self::REMOTE_IMAGE_PATH);
        $this->assertEquals(35, $result->getPixelHeight());
        $this->assertEquals(59, $result->getPixelWidth());
        $this->assertInstanceOf(Zend_Pdf_Resource_Image_Png::class, $result);
    }
}
