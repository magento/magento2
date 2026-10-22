<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\MediaGallerySynchronization\Test\Unit\Model;

use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Filesystem\ExtendedDriverInterface;
use Magento\MediaGalleryApi\Api\Data\AssetInterface;
use Magento\MediaGalleryApi\Api\Data\AssetInterfaceFactory;
use Magento\MediaGallerySynchronization\Model\CreateAssetFromFile;
use Magento\MediaGallerySynchronization\Model\Filesystem\GetFileInfo;
use Magento\MediaGallerySynchronization\Model\GetContentHash;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CreateAssetFromFileTest extends TestCase
{
    private const PATH = 'catalog/product/image.png';
    private const ABSOLUTE_PATH = '/media/catalog/product/image.png';

    /**
     * @var ExtendedDriverInterface|MockObject
     */
    private $driver;

    /**
     * @var AssetInterfaceFactory|MockObject
     */
    private $assetFactory;

    /**
     * @var CreateAssetFromFile
     */
    private $model;

    protected function setUp(): void
    {
        $this->driver = $this->createMock(ExtendedDriverInterface::class);
        $mediaDirectory = $this->createMock(WriteInterface::class);
        $mediaDirectory->method('getAbsolutePath')->with(self::PATH)->willReturn(self::ABSOLUTE_PATH);
        $mediaDirectory->method('getDriver')->willReturn($this->driver);
        $mediaDirectory->method('readFile')->willReturn('content');
        $filesystem = $this->createMock(Filesystem::class);
        $filesystem->method('getDirectoryWrite')->willReturn($mediaDirectory);
        $getContentHash = $this->createMock(GetContentHash::class);
        $getContentHash->method('execute')->willReturn('hash');
        $this->assetFactory = $this->createMock(AssetInterfaceFactory::class);

        $this->model = new CreateAssetFromFile(
            $filesystem,
            $this->createMock(File::class),
            $this->assetFactory,
            $getContentHash,
            $this->createMock(GetFileInfo::class)
        );
    }

    public function testExecuteUsesDimensionsFromExtendedDriverMetadata(): void
    {
        $this->driver->method('getMetadata')->willReturn([
            'basename' => 'image',
            'size' => 100,
            'extension' => 'png',
            'extra' => ['image-width' => 7, 'image-height' => 9]
        ]);
        $this->driver->expects($this->never())->method('fileGetContents');

        $this->assertAssetDimensions(7, 9);
        $this->model->execute(self::PATH);
    }

    public function testExecuteReadsDimensionsFromContentWhenExtendedDriverMetadataHasNone(): void
    {
        $this->driver->method('getMetadata')->willReturn([
            'basename' => 'image',
            'size' => 100,
            'extension' => 'png'
        ]);
        $this->driver->method('fileGetContents')->with(self::ABSOLUTE_PATH)->willReturn($this->createPng(4, 3));

        $this->assertAssetDimensions(4, 3);
        $this->model->execute(self::PATH);
    }

    public function testExecuteKeepsZeroDimensionsWhenContentIsNotAnImage(): void
    {
        $this->driver->method('getMetadata')->willReturn(['basename' => 'image', 'size' => 100]);
        $this->driver->method('fileGetContents')->willReturn('not an image');

        $this->assertAssetDimensions(0, 0);
        $this->model->execute(self::PATH);
    }

    private function assertAssetDimensions(int $width, int $height): void
    {
        $this->assetFactory->expects($this->once())
            ->method('create')
            ->with($this->callback(
                static fn (array $data): bool => $data['width'] === $width && $data['height'] === $height
            ))
            ->willReturn($this->createMock(AssetInterface::class));
    }

    private function createPng(int $width, int $height): string
    {
        $image = imagecreatetruecolor($width, $height);
        ob_start();
        imagepng($image);
        return (string)ob_get_clean();
    }
}
