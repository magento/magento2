<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Lock\Test\Unit\Backend;

use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\RuntimeException;
use Magento\Framework\Filesystem\Driver\File as FileDriver;
use Magento\Framework\Lock\Backend\FileLock;
use Magento\Framework\Phrase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Tests for resolving the file lock storage directory.
 */
class FileLockTest extends TestCase
{
    /**
     * The directory and lock file paths must be independent of directory existence.
     */
    #[DataProvider('pathProvider')]
    public function testStoragePath(string $path, string $expectedPath, bool $exists): void
    {
        $driver = $this->createMock(FileDriver::class);
        $driver->expects($this->never())->method('getRealPath');
        $driver->expects($this->exactly(2))->method('isExists')->willReturnMap([
            [$expectedPath, $exists],
            [$expectedPath . hash('sha256', 'test_lock'), false],
        ]);
        $driver->expects($exists ? $this->never() : $this->once())
            ->method('createDirectory')
            ->with($expectedPath);

        $lock = new FileLock($driver, $path);
        self::assertFalse($lock->isLocked('test_lock'));
    }

    /**
     * Provide path forms with existing and missing storage directories.
     */
    public static function pathProvider(): array
    {
        $windows = DIRECTORY_SEPARATOR === '\\';
        $paths = [
            'relative' => ['var/locks', BP . '/var/locks/'],
            'relative trailing slash' => ['var/locks/', BP . '/var/locks/'],
            'relative dot' => ['./var/locks', BP . '/./var/locks/'],
            'relative colon' => ['var:locks', BP . '/var:locks/'],
            'drive relative' => ['c:locks', BP . '/c:locks/'],
            'invalid URL port' => ['a:99999/x', BP . '/a:99999/x/'],
            'absolute' => ['/tmp/magento-locks', '/tmp/magento-locks/'],
            'absolute trailing slash' => ['/tmp/magento-locks/', '/tmp/magento-locks/'],
            'root' => ['/', '/'],
            'windows drive' => ['C:/locks', 'C:/locks/'],
            'windows backslashes' => ['C:\\locks\\', $windows ? 'C:/locks/' : BP . '/C:\\locks\\/'],
            'network share' => [
                '\\\\server\\share\\locks',
                $windows ? '//server/share/locks/' : BP . '/\\\\server\\share\\locks/',
            ],
            'stream wrapper' => ['file:///tmp/magento-locks', 'file:///tmp/magento-locks/'],
        ];
        $cases = [];
        foreach ($paths as $name => [$path, $expectedPath]) {
            foreach ([true, false] as $exists) {
                $caseName = $name . ($exists ? ' existing' : ' missing');
                $cases[$caseName] = [$path, $expectedPath, $exists];
            }
        }

        return $cases;
    }

    /**
     * Empty configuration still fails before accessing the filesystem.
     */
    public function testEmptyPath(): void
    {
        $driver = $this->createMock(FileDriver::class);
        $driver->expects($this->never())->method('isExists');
        $driver->expects($this->never())->method('createDirectory');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('The path needs to be a non-empty string.');

        new FileLock($driver, '');
    }

    /**
     * Filesystem failures retain their cause and report the resolved directory.
     */
    #[DataProvider('failureProvider')]
    public function testFilesystemFailure(string $operation): void
    {
        $driver = $this->createMock(FileDriver::class);
        $exception = new FileSystemException(new Phrase('Filesystem failure.'));
        $driver->expects($this->once())->method($operation)
            ->with(BP . '/var/locks/')
            ->willThrowException($exception);
        if ($operation === 'createDirectory') {
            $driver->expects($this->once())->method('isExists')
                ->with(BP . '/var/locks/')
                ->willReturn(false);
        } else {
            $driver->expects($this->never())->method('createDirectory');
        }

        try {
            new FileLock($driver, 'var/locks');
            self::fail('A filesystem failure must be wrapped in a runtime exception.');
        } catch (RuntimeException $actual) {
            self::assertSame($exception, $actual->getPrevious());
            self::assertSame('Cannot create the directory for locks: ' . BP . '/var/locks/', $actual->getMessage());
        }
    }

    /**
     * Cover both filesystem operations used during construction.
     */
    public static function failureProvider(): array
    {
        return [
            'existence check' => ['isExists'],
            'directory creation' => ['createDirectory'],
        ];
    }
}
