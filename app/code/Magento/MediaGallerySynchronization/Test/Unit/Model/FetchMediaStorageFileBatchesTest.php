<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\MediaGallerySynchronization\Test\Unit\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\Read;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Phrase;
use Magento\MediaGallery\Model\Directory\IsExcluded;
use Magento\MediaGalleryApi\Api\IsPathExcludedInterface;
use Magento\MediaGalleryApi\Model\ExcludedPatternsConfigInterface;
use Magento\MediaGallerySynchronization\Model\FetchMediaStorageFileBatches;
use Magento\MediaGallerySynchronization\Model\GetAssetsIterator;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Verify media scans remain lazy while preserving the synchronized paths and their order.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FetchMediaStorageFileBatchesTest extends TestCase
{
    /**
     * Directory visits recorded by the fixture reader, including the baseline recursive scan.
     *
     * @var string[]
     */
    private array $reads = [];

    public function testFirstBatchDoesNotReadLaterDirectories(): void
    {
        $subject = $this->createSubject([
            '' => ['z-custom', 'wysiwyg', 'tmp'],
            'tmp' => ['tmp/ignored.png'],
            'wysiwyg' => ['wysiwyg/b.png', 'wysiwyg/a.png'],
            'z-custom' => ['z-custom/later.png']
        ], ['wysiwyg', 'z-custom']);

        $batches = $subject->execute();
        $batches->rewind();

        self::assertSame(['wysiwyg/a.png', 'wysiwyg/b.png'], $batches->current());
        self::assertSame(['', 'wysiwyg'], $this->reads);
    }

    public function testExcludedDirectoriesAreNotRead(): void
    {
        $subject = $this->createSubject([
            '' => ['catalog', 'import', 'tmp', 'wysiwyg'],
            'catalog' => ['catalog/product', 'catalog/category'],
            'catalog/category' => ['catalog/category/category.png'],
            'catalog/product' => ['catalog/product/cache'],
            'catalog/product/cache' => ['catalog/product/cache/resized.png'],
            'import' => ['import/import.png'],
            'tmp' => ['tmp/tmp.png'],
            'wysiwyg' => ['wysiwyg/image.png']
        ], ['catalog/category', 'wysiwyg']);

        self::assertSame([
            ['catalog/category/category.png', 'wysiwyg/image.png']
        ], iterator_to_array($subject->execute(), false));
        self::assertSame(['', 'catalog', 'catalog/category', 'wysiwyg'], $this->reads);
    }

    public function testMatchesRecursiveScanFileSetOrderAndBatchSizes(): void
    {
        $tree = [
            '' => ['custom', 'catalog', 'tmp'],
            'catalog' => ['catalog/category', 'catalog/product'],
            'catalog/category' => ['catalog/category/image.JPG'],
            'catalog/product' => ['catalog/product/image.png'],
            'custom' => [
                'custom/a', 'custom/a.png', 'custom/a-.png', 'custom/folder.jpg', 'custom/readme.txt', 'custom/z.png'
            ],
            'custom/a' => ['custom/a/image.gif'],
            'custom/folder.jpg' => ['custom/folder.jpg/child.jpeg'],
            'tmp' => ['tmp/ignored.png']
        ];
        $subject = $this->createSubject($tree, ['catalog/category', 'custom']);
        $paths = array_merge(...array_values($tree));
        sort($paths);
        $expected = array_values(array_filter(
            $paths,
            static fn (string $path): bool => preg_match('#^(catalog/category|custom)\\b(?!-)#', $path) === 1
                && preg_match('#\\.(jpg|jpeg|gif|png)$#i', $path) === 1
        ));

        $batches = iterator_to_array($subject->execute(), false);

        self::assertSame($expected, array_merge(...$batches));
        self::assertSame([2, 2, 2, 1], array_map('count', $batches));
    }

    public function testRegexFolderConfigurationKeepsMatchingDescendants(): void
    {
        $subject = $this->createSubject([
            '' => ['custom'],
            'custom' => ['custom/images'],
            'custom/images' => ['custom/images/image.png']
        ], ['custom/(images|photos)']);

        self::assertSame([['custom/images/image.png']], iterator_to_array($subject->execute(), false));
    }

    public function testCustomExclusionServiceCanAllowDescendants(): void
    {
        $excluded = $this->createStub(IsPathExcludedInterface::class);
        $excluded->method('execute')->willReturnCallback(static fn (string $path): bool => $path === 'custom');
        $subject = $this->createSubject([
            '' => ['custom'],
            'custom' => ['custom/image.png']
        ], ['wysiwyg'], $excluded);

        self::assertSame([['custom/image.png']], iterator_to_array($subject->execute(), false));
    }

    public function testFolderWithTrailingSlashKeepsItsParentDirectory(): void
    {
        $subject = $this->createSubject([
            '' => ['custom'],
            'custom' => ['custom/image.png']
        ], ['custom/']);

        self::assertSame([['custom/image.png']], iterator_to_array($subject->execute(), false));
    }

    public function testEmptyScanProducesNoBatch(): void
    {
        self::assertSame([], iterator_to_array($this->createSubject(['' => []], ['wysiwyg'])->execute(), false));
    }

    public function testNonLocalDriverUsesRecursiveScanWithoutDirectoryProbes(): void
    {
        $directory = $this->createMock(Read::class);
        $directory->method('getAbsolutePath')->willReturn('/media/');
        $directory->expects(self::never())->method('read');
        $directory->expects(self::never())->method('isDirectory');
        $directory->expects(self::once())->method('readRecursively')->with('/media/')->willReturn([
            'catalog/product/ignored.png',
            'wysiwyg/a.png',
            'wysiwyg/b.JPG',
            'wysiwyg/c.gif',
            'wysiwyg/readme.txt'
        ]);
        $remoteDriver = $this->createMock(DriverInterface::class);
        $remoteDriver->expects(self::never())->method('isDirectory');
        $remoteDriver->expects(self::never())->method('readDirectory');
        $write = $this->createStub(WriteInterface::class);
        $write->method('getDriver')->willReturn($remoteDriver);
        $filesystem = $this->createMock(Filesystem::class);
        $filesystem->expects(self::once())->method('getDirectoryRead')->with(DirectoryList::MEDIA)
            ->willReturn($directory);
        $filesystem->expects(self::once())->method('getDirectoryWrite')->with(DirectoryList::MEDIA)
            ->willReturn($write);
        $excluded = $this->createStub(IsPathExcludedInterface::class);
        $excluded->method('execute')->willReturnCallback(
            static fn (string $path): bool => str_starts_with($path, 'catalog/')
        );
        $config = $this->createMock(ScopeConfigInterface::class);
        $config->expects(self::never())->method('getValue');
        $subject = new FetchMediaStorageFileBatches(
            $this->createStub(LoggerInterface::class),
            $excluded,
            $filesystem,
            $this->createStub(GetAssetsIterator::class),
            new File(),
            2,
            ['jpg', 'jpeg', 'gif', 'png'],
            $config
        );

        self::assertSame([
            ['wysiwyg/a.png', 'wysiwyg/b.JPG'],
            ['wysiwyg/c.gif']
        ], iterator_to_array($subject->execute(), false));
    }

    public function testEntryStartingWithDashDoesNotStopSynchronization(): void
    {
        $subject = $this->createSubject([
            '' => ['-cache', 'wysiwyg'],
            '-cache' => ['-cache/cached.png'],
            'wysiwyg' => ['wysiwyg/image.png']
        ], ['wysiwyg', '-cache'], null);

        self::assertSame(
            [['-cache/cached.png', 'wysiwyg/image.png']],
            iterator_to_array($subject->execute(), false)
        );
    }

    private function rejectRelativeDashPath(string $path): void
    {
        if (str_starts_with($path, '-')) {
            throw new ValidatorException(new Phrase('Path "%1" cannot be used with directory "/media/"', [$path]));
        }
    }

    private function toRelative(string $path): string
    {
        return str_starts_with($path, '/media/') ? substr($path, strlen('/media/')) : $path;
    }

    private function createSubject(
        array $tree,
        array $folders,
        ?IsPathExcludedInterface $excluded = null
    ): FetchMediaStorageFileBatches {
        $this->reads = [];
        $directory = $this->createStub(Read::class);
        $driver = new File();
        $directory->method('getAbsolutePath')->willReturn('/media/');
        $directory->method('read')->willReturnCallback(function ($path) use ($tree): array {
            $this->rejectRelativeDashPath($path);
            $path = $this->toRelative($path);
            $this->reads[] = $path;
            self::assertArrayHasKey($path, $tree);
            return $tree[$path];
        });
        $directory->method('isDirectory')->willReturnCallback(
            function (string $path) use ($tree): bool {
                $this->rejectRelativeDashPath($path);
                return isset($tree[$this->toRelative($path)]);
            }
        );
        $directory->method('readRecursively')->willReturnCallback(function () use ($tree): array {
            $this->reads = array_keys($tree);
            $paths = array_merge(...array_values($tree));
            sort($paths);
            return $paths;
        });
        $filesystem = $this->createMock(Filesystem::class);
        $filesystem->expects(self::once())->method('getDirectoryRead')->with(DirectoryList::MEDIA)
            ->willReturn($directory);
        $config = $this->createStub(ScopeConfigInterface::class);
        $config->method('getValue')->willReturn($folders);
        $write = $this->createStub(WriteInterface::class);
        $write->method('getDriver')->willReturn($driver);
        $filesystem->method('getDirectoryWrite')->willReturn($write);
        if ($excluded === null) {
            $excluded = new IsExcluded(
                $this->createStub(ExcludedPatternsConfigInterface::class),
                $filesystem,
                $config
            );
        }

        return new FetchMediaStorageFileBatches(
            $this->createStub(LoggerInterface::class),
            $excluded,
            $filesystem,
            $this->createStub(GetAssetsIterator::class),
            $driver,
            2,
            ['jpg', 'jpeg', 'gif', 'png'],
            $config
        );
    }
}
