<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\MediaGallerySynchronization\Test\Unit\Model;

use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\DateTimeFactory;
use Magento\MediaGalleryApi\Api\Data\AssetInterface;
use Magento\MediaGalleryApi\Api\GetAssetsByPathsInterface;
use Magento\MediaGallerySynchronization\Model\Filesystem\FileInfo;
use Magento\MediaGallerySynchronization\Model\Filesystem\GetFileInfo;
use Magento\MediaGallerySynchronization\Model\SynchronizeFiles;
use Magento\MediaGallerySynchronizationApi\Model\ImportFilesInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Test for SynchronizeFiles
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SynchronizeFilesTest extends TestCase
{
    private const UPDATED_AT = '2026-01-01 00:00:00';

    /**
     * @var GetFileInfo|Stub
     */
    private $getFileInfo;

    /**
     * @var GetAssetsByPathsInterface|Stub
     */
    private $getAssetsByPaths;

    /**
     * @var ImportFilesInterface|MockObject
     */
    private $importFiles;

    /**
     * @var LoggerInterface|MockObject
     */
    private $log;

    /**
     * @var SynchronizeFiles
     */
    private $synchronizeFiles;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $driver = $this->createStub(File::class);
        $driver->method('getParentDirectory')->willReturn('wysiwyg');

        $mediaDirectory = $this->createStub(ReadInterface::class);
        $mediaDirectory->method('getAbsolutePath')->willReturnCallback(
            static fn (string $path): string => '/media/' . $path
        );
        $filesystem = $this->createStub(Filesystem::class);
        $filesystem->method('getDirectoryRead')->willReturn($mediaDirectory);

        $dateTime = $this->createStub(DateTime::class);
        $dateTime->method('gmtDate')->willReturnCallback(
            static fn (string $format, int $time): string => gmdate($format, $time)
        );
        $dateFactory = $this->createStub(DateTimeFactory::class);
        $dateFactory->method('create')->willReturn($dateTime);

        $this->getFileInfo = $this->createStub(GetFileInfo::class);
        $this->getAssetsByPaths = $this->createStub(GetAssetsByPathsInterface::class);
        $this->importFiles = $this->createMock(ImportFilesInterface::class);
        $this->log = $this->createMock(LoggerInterface::class);

        $this->synchronizeFiles = new SynchronizeFiles(
            $driver,
            $filesystem,
            $dateFactory,
            $this->log,
            $this->getFileInfo,
            $this->getAssetsByPaths,
            $this->importFiles
        );
    }

    /**
     * A known asset whose file cannot be stat-ed is skipped; the other files of the batch are still imported.
     */
    public function testExecuteSkipsKnownAssetThatCannotBeRead(): void
    {
        $readError = new \RuntimeException('SplFileInfo::getSize(): stat failed for /media/wysiwyg/gone.jpg');
        $this->getAssetsByPaths->method('execute')->willReturn(
            [
                $this->createAsset('wysiwyg/unchanged.jpg'),
                $this->createAsset('wysiwyg/gone.jpg'),
                $this->createAsset('wysiwyg/changed.jpg'),
            ]
        );
        $this->getFileInfo->method('execute')->willReturnCallback(
            function (string $absolutePath) use ($readError): FileInfo {
                $fileInfo = $this->createStub(FileInfo::class);
                switch ($absolutePath) {
                    case '/media/wysiwyg/unchanged.jpg':
                        $fileInfo->method('getMTime')->willReturn(strtotime(self::UPDATED_AT . ' UTC'));
                        return $fileInfo;
                    case '/media/wysiwyg/gone.jpg':
                        throw $readError;
                    default:
                        $fileInfo->method('getMTime')->willReturn(strtotime(self::UPDATED_AT . ' UTC') + 60);
                        return $fileInfo;
                }
            }
        );

        $this->log->expects($this->once())
            ->method('warning')
            ->with($this->isString(), ['path' => 'wysiwyg/gone.jpg', 'exception' => $readError]);
        $this->log->expects($this->never())->method('critical');
        $this->importFiles->expects($this->once())
            ->method('execute')
            ->with(
                [
                    'wysiwyg/changed.jpg' => 'wysiwyg/changed.jpg',
                    'wysiwyg/new.jpg' => 'wysiwyg/new.jpg',
                ]
            );

        $this->synchronizeFiles->execute(
            ['wysiwyg/unchanged.jpg', 'wysiwyg/gone.jpg', 'wysiwyg/changed.jpg', 'wysiwyg/new.jpg']
        );
    }

    /**
     * Create an existing asset mock
     *
     * @param string $path
     * @return AssetInterface|Stub
     */
    private function createAsset(string $path): AssetInterface
    {
        $asset = $this->createStub(AssetInterface::class);
        $asset->method('getPath')->willReturn($path);
        $asset->method('getUpdatedAt')->willReturn(self::UPDATED_AT);

        return $asset;
    }
}
