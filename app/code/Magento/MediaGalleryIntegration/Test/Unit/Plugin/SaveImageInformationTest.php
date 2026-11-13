<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryIntegration\Test\Unit\Plugin;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\File\Uploader;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\MediaGalleryApi\Api\IsPathExcludedInterface;
use Magento\MediaGalleryIntegration\Plugin\SaveImageInformation;
use Magento\MediaGallerySynchronizationApi\Api\SynchronizeFilesInterface;
use Magento\MediaGalleryUiApi\Api\ConfigInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Verify uploaded image eligibility and path normalization.
 */
class SaveImageInformationTest extends TestCase
{
    #[DataProvider('uploadProvider')]
    public function testAfterSave(
        bool $enabled,
        array $result,
        ?string $relativePath,
        bool $excluded,
        bool $throws,
        bool $synchronize
    ): void {
        $directory = $this->createMock(ReadInterface::class);
        $directory->expects($this->once())->method('getAbsolutePath')->willReturn('/pub/media/');
        $directory->expects($relativePath === null ? $this->never() : $this->once())
            ->method('getRelativePath')
            ->with('/pub/media/' . $relativePath)
            ->willReturn($relativePath ?? '');

        $filesystem = $this->createMock(Filesystem::class);
        $filesystem->expects($this->exactly($relativePath === null ? 1 : 2))
            ->method('getDirectoryRead')->with(DirectoryList::MEDIA)->willReturn($directory);
        $config = $this->createMock(ConfigInterface::class);
        $config->expects($this->once())->method('isEnabled')->willReturn($enabled);
        $exception = new \Exception('Cannot check path');
        $exclusion = $this->createMock(IsPathExcludedInterface::class);
        $expectation = $exclusion->expects($relativePath === null ? $this->never() : $this->once())
            ->method('execute')->with($relativePath);
        if ($throws) {
            $expectation->willThrowException($exception);
        } else {
            $expectation->willReturn($excluded);
        }
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($throws ? $this->once() : $this->never())->method('critical')->with($exception);
        $synchronizer = $this->createMock(SynchronizeFilesInterface::class);
        $synchronizer->expects($synchronize ? $this->once() : $this->never())
            ->method('execute')->with([$relativePath]);

        $plugin = new SaveImageInformation($filesystem, $logger, $exclusion, $synchronizer, $config, ['jpg']);

        self::assertSame($result, $plugin->afterSave($this->createMock(Uploader::class), $result));
    }

    public static function uploadProvider(): array
    {
        return [
            'disabled' => [false, ['path' => '/pub/media/', 'file' => 'image.jpg'], null, false, false, false],
            'outside media' => [true, ['path' => '/tmp/', 'file' => 'image.jpg'], null, false, false, false],
            'missing path' => [true, ['file' => 'image.jpg'], null, false, false, false],
            'media root without slash' => [
                true, ['path' => '/pub/media', 'file' => '/image.jpg'], null, false, false, false
            ],
            'media root' => [
                true, ['path' => '/pub/media/', 'file' => 'image.jpg'], 'image.jpg', false, false, true
            ],
            'media root with leading file slash' => [
                true, ['path' => '/pub/media/', 'file' => '/image.jpg'], 'image.jpg', false, false, true
            ],
            'subdirectory without slash' => [
                true, ['path' => '/pub/media/catalog', 'file' => 'image.jpg'], 'catalog/image.jpg', false, false, true
            ],
            'subdirectory with slashes' => [
                true, ['path' => '/pub/media/catalog/', 'file' => '/image.jpg'], 'catalog/image.jpg', false, false, true
            ],
            'excluded' => [
                true, ['path' => '/pub/media/', 'file' => 'image.jpg'], 'image.jpg', true, false, false
            ],
            'non-image extension' => [
                true, ['path' => '/pub/media/', 'file' => 'image.txt'], 'image.txt', false, false, false
            ],
            'exclusion throws' => [
                true, ['path' => '/pub/media/', 'file' => 'image.jpg'], 'image.jpg', false, true, false
            ],
        ];
    }
}
