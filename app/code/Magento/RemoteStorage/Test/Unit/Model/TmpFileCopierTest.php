<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\RemoteStorage\Test\Unit\Model;

use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\TargetDirectory;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\RemoteStorage\Model\Config;
use Magento\RemoteStorage\Model\TmpFileCopier;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class TmpFileCopierTest extends TestCase
{
    /**
     * @var WriteInterface|Stub
     */
    private $tmpDirectoryWrite;

    /**
     * @var WriteInterface|Stub
     */
    private $remoteDirectoryWrite;

    /**
     * @var string[]
     */
    private $written = [];

    /**
     * @var int
     */
    private $remoteReads = 0;

    /**
     * @var string[]
     */
    private $deleted = [];

    protected function setUp(): void
    {
        $remoteDriver = $this->createStub(DriverInterface::class);
        $remoteDriver->method('fileGetContents')
            ->willReturnCallback(function (string $path): string {
                $this->remoteReads++;
                return 'content of ' . $path;
            });
        $tmpDriver = $this->createStub(DriverInterface::class);
        $tmpDriver->method('filePutContents')
            ->willReturnCallback(function (string $path, string $content): int {
                $this->written[$path] = $content;
                return strlen($content);
            });
        $this->remoteDirectoryWrite = $this->createStub(WriteInterface::class);
        $this->remoteDirectoryWrite->method('getAbsolutePath')
            ->willReturnCallback(static fn (string $path): string => '/remote/' . $path);
        $this->remoteDirectoryWrite->method('isFile')->willReturn(true);
        $this->remoteDirectoryWrite->method('getDriver')->willReturn($remoteDriver);
        $this->tmpDirectoryWrite = $this->createStub(WriteInterface::class);
        $this->tmpDirectoryWrite->method('getAbsolutePath')->willReturn('/var/tmp/');
        $this->tmpDirectoryWrite->method('getDriver')->willReturn($tmpDriver);
        $this->tmpDirectoryWrite->method('isFile')
            ->willReturnCallback(fn (string $path): bool => isset($this->written[$path]));
        $this->tmpDirectoryWrite->method('delete')
            ->willReturnCallback(function (string $path): void {
                $this->deleted[] = $path;
                unset($this->written[$path]);
            });
    }

    public function testCopyUsesDistinctTmpFilesForSameBasename(): void
    {
        $copier = $this->createCopier();

        $first = $copier->copy('catalog/product/a/b/image.jpg');
        $second = $copier->copy('catalog/product/c/d/image.jpg');

        self::assertNotSame($first, $second);
        self::assertStringEndsWith('.jpg', $first);
        self::assertSame('content of catalog/product/a/b/image.jpg', $this->written[$first]);
        self::assertSame('content of catalog/product/c/d/image.jpg', $this->written[$second]);
    }

    public function testCopyUsesDistinctTmpFilesPerInstance(): void
    {
        $first = $this->createCopier()->copy('catalog/product/a/b/image.jpg');
        $second = $this->createCopier()->copy('catalog/product/a/b/image.jpg');

        self::assertNotSame($first, $second);
    }

    public function testSecondCopyOfSamePathReturnsCachedTmpFileAndReadsRemoteOnce(): void
    {
        $copier = $this->createCopier();

        $first = $copier->copy('catalog/product/a/b/image.jpg');
        $second = $copier->copy('catalog/product/a/b/image.jpg');

        self::assertSame($first, $second);
        self::assertSame(1, $this->remoteReads);
    }

    public function testCopyReCopiesWhenCachedTmpFileWasRemoved(): void
    {
        $copier = $this->createCopier();

        $first = $copier->copy('catalog/product/a/b/image.jpg');
        unset($this->written[$first]);
        $second = $copier->copy('catalog/product/a/b/image.jpg');

        self::assertSame($first, $second);
        self::assertSame(2, $this->remoteReads);
    }

    public function testDestructorDeletesCreatedTmpFile(): void
    {
        $copier = $this->createCopier();

        $tmpFile = $copier->copy('catalog/product/a/b/image.jpg');
        unset($copier);

        self::assertSame([$tmpFile], $this->deleted);
    }

    private function createCopier(): TmpFileCopier
    {
        $filesystem = $this->createStub(Filesystem::class);
        $filesystem->method('getDirectoryWrite')->willReturn($this->tmpDirectoryWrite);
        $targetDirectory = $this->createStub(TargetDirectory::class);
        $targetDirectory->method('getDirectoryWrite')->willReturn($this->remoteDirectoryWrite);
        $config = $this->createStub(Config::class);
        $config->method('isEnabled')->willReturn(true);

        return new TmpFileCopier(
            $filesystem,
            $targetDirectory,
            $config,
            $this->createStub(LoggerInterface::class)
        );
    }
}
