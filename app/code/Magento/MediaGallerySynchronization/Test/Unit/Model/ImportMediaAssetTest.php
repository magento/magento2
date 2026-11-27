<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\MediaGallerySynchronization\Test\Unit\Model;

use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\MediaGalleryApi\Api\Data\AssetInterface;
use Magento\MediaGalleryApi\Api\SaveAssetsInterface;
use Magento\MediaGallerySynchronization\Model\GetAssetFromPath;
use Magento\MediaGallerySynchronization\Model\ImportMediaAsset;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Test for ImportMediaAsset
 */
class ImportMediaAssetTest extends TestCase
{
    /**
     * @var SaveAssetsInterface|MockObject
     */
    private $saveAssets;

    /**
     * @var GetAssetFromPath|Stub
     */
    private $getAssetFromPath;

    /**
     * @var LoggerInterface|MockObject
     */
    private $log;

    /**
     * @var ImportMediaAsset
     */
    private $importMediaAsset;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->saveAssets = $this->createMock(SaveAssetsInterface::class);
        $this->getAssetFromPath = $this->createStub(GetAssetFromPath::class);
        $this->log = $this->createMock(LoggerInterface::class);
        $this->importMediaAsset = new ImportMediaAsset($this->saveAssets, $this->getAssetFromPath, $this->log);
    }

    /**
     * A file that cannot be read is skipped and the rest of the batch is still saved.
     *
     * @param \Exception $readError
     */
    #[DataProvider('readErrorProvider')]
    public function testExecuteSkipsFileThatCannotBeRead(\Exception $readError): void
    {
        $first = $this->createStub(AssetInterface::class);
        $last = $this->createStub(AssetInterface::class);
        $this->getAssetFromPath->method('execute')->willReturnCallback(
            static function (string $path) use ($first, $last, $readError): AssetInterface {
                return match ($path) {
                    'wysiwyg/first.jpg' => $first,
                    'wysiwyg/broken.jpg' => throw $readError,
                    'wysiwyg/last.jpg' => $last,
                };
            }
        );

        $this->log->expects($this->once())
            ->method('warning')
            ->with(
                $this->isString(),
                ['path' => 'wysiwyg/broken.jpg', 'exception' => $readError]
            );
        $this->saveAssets->expects($this->once())
            ->method('execute')
            ->with([$first, $last]);

        $this->importMediaAsset->execute(['wysiwyg/first.jpg', 'wysiwyg/broken.jpg', 'wysiwyg/last.jpg']);
    }

    /**
     * @return array
     */
    public static function readErrorProvider(): array
    {
        return [
            'stat failed' => [
                new \RuntimeException('SplFileInfo::getSize(): stat failed for /media/wysiwyg/broken.jpg')
            ],
            'unreadable file' => [
                new FileSystemException(new Phrase('The file "/media/wysiwyg/broken.jpg" doesn\'t exist.'))
            ],
        ];
    }

    /**
     * Errors other than an unreadable file still abort the batch.
     */
    public function testExecuteDoesNotHideOtherErrors(): void
    {
        $this->getAssetFromPath->method('execute')
            ->willThrowException(new LocalizedException(new Phrase('Could not get media assets for paths')));

        $this->log->expects($this->never())->method('warning');
        $this->saveAssets->expects($this->never())->method('execute');
        $this->expectException(LocalizedException::class);

        $this->importMediaAsset->execute(['wysiwyg/first.jpg']);
    }
}
