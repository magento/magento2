<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Filesystem\Driver;

use Magento\Framework\Exception\FileSystemException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Magento\Framework\Filesystem\Driver\File
 * Verify File class
 */
class FileTest extends TestCase
{
    /**
     * @var File
     */
    private $driver;

    /**
     * @var String
     */
    private $absolutePath;

    /**
     * @var String
     */
    private $generatedPath;

    /**
     * @var int|null
     */
    private $originalUmask;

    /**
     * Returns relative path for the test.
     *
     * @param $relativePath
     * @return string
     */
    protected function getTestPath($relativePath)
    {
        return $this->absolutePath . $relativePath;
    }

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->driver = new File();
        $this->absolutePath = dirname(__DIR__) . '/_files/';
        $this->generatedPath = $this->getTestPath('generated');
        $this->removeGeneratedDirectory();
    }

    /**
     * @inheritdoc
     *
     * @return void
     */
    protected function tearDown(): void
    {
        if ($this->originalUmask !== null) {
            umask($this->originalUmask);
            $this->originalUmask = null;
        }
        $this->removeGeneratedDirectory();
    }

    /**
     * Tests read directory with symlynked folders.
     *
     * @return void
     */
    public function testReadDirectoryRecursivelyWithSymlinkedFolders(): void
    {
        $sourceDirectory = $this->generatedPath . '/source';
        $destinationDirectory = $this->generatedPath . '/destination';

        $this->driver->createDirectory($sourceDirectory);
        $this->driver->createDirectory($sourceDirectory . '/directory1');
        $this->driver->createDirectory($destinationDirectory);

        $linkName = $destinationDirectory . '/link';
        $this->driver->symlink($sourceDirectory, $linkName);

        $paths = [
            $destinationDirectory . '/link' . '/directory1',
            $destinationDirectory . '/link'

        ];
        $actual = $this->driver->readDirectoryRecursively($destinationDirectory);
        $this->assertEquals($paths, $actual);
    }

    /**
     * Tests directory recursive read.
     *
     * @return void
     */
    public function testReadDirectoryRecursively(): void
    {
        $paths = [
            'foo/bar',
            'foo/bar/baz',
            'foo/bar/baz/file_one.txt',
            'foo/bar/file_two.txt',
            'foo/file_three.txt',
        ];
        $expected = array_map([self::class, 'getTestPath'], $paths);
        $actual = $this->driver->readDirectoryRecursively($this->getTestPath('foo'));
        sort($actual);
        $this->assertEquals($expected, $actual);
    }

    /**
     * Tests directory reading exception.
     *
     * @return void
     */
    public function testReadDirectoryRecursivelyFailure(): void
    {
        $this->expectException(\Magento\Framework\Exception\FileSystemException::class);

        $this->driver->readDirectoryRecursively($this->getTestPath('not-existing-directory'));
    }

    /**
     * Tests of directory creating.
     *
     * @throws FileSystemException
     * @return void
     */
    public function testCreateDirectory(): void
    {
        $generatedPath = $this->getTestPath('generated/roo/bar/baz/foo');
        $generatedPathBase = $this->getTestPath('generated');
        // Delete the generated directory if it already exists
        if (is_dir($generatedPath)) {
            $this->assertTrue($this->driver->deleteDirectory($generatedPathBase));
        }
        $this->assertTrue($this->driver->createDirectory($generatedPath));
        $this->assertTrue(is_dir($generatedPath));
    }

    /**
     * Tests creation and removing of symlinks.
     *
     * @throws FileSystemException
     * @return void
     */
    public function testSymlinks(): void
    {
        $sourceDirectory = $this->generatedPath . '/source';
        $destinationDirectory = $this->generatedPath . '/destination';

        $this->driver->createDirectory($sourceDirectory);
        $this->driver->createDirectory($destinationDirectory);

        $linkName = $destinationDirectory . '/link';

        self::assertTrue($this->driver->isWritable($destinationDirectory));
        self::assertTrue($this->driver->symlink($sourceDirectory, $linkName));
        self::assertTrue($this->driver->isExists($linkName));
        self::assertTrue($this->driver->deleteDirectory($linkName));
    }

    /**
     * Verify file put content without content.
     *
     * @return void
     * @throws FileSystemException
     */
    public function testFilePutWithoutContents(): void
    {
        $path = $this->absolutePath . 'foo/file_three.txt';
        $this->assertEquals(0, $this->driver->filePutContents($path, ''));
    }

    /**
     * Delete a not existing file
     *
     * @return void
     * @throws FileSystemException
     */
    public function testDeleteFileEdge(): void
    {
        $path = $this->absolutePath . 'foo/file_four.txt';
        $this->assertEquals(true, $this->driver->deleteFile($path));
    }

    /**
     * Renamed files must not receive execute bits; content must be preserved.
     *
     * @param int $umask
     * @param bool $stateful
     * @param int $expectedMode
     * @return void
     * @throws FileSystemException
     */
    #[DataProvider('renameFilePermissionsDataProvider')]
    public function testRenameFileAppliesFilePermissions(int $umask, bool $stateful, int $expectedMode): void
    {
        $driver = new File($stateful);
        $driver->createDirectory($this->generatedPath);
        $source = $this->generatedPath . '/source.txt';
        $target = $this->generatedPath . '/target.txt';
        $driver->filePutContents($source, 'file content');
        $driver->changePermissions($source, 0600);

        $this->originalUmask = umask($umask);
        self::assertTrue($driver->rename($source, $target));

        self::assertFalse($driver->isExists($source));
        self::assertSame('file content', $driver->fileGetContents($target));
        clearstatcache(true, $target);
        self::assertSame(sprintf('%04o', $expectedMode), sprintf('%04o', fileperms($target) & 0777));
    }

    /**
     * @return array
     */
    public static function renameFilePermissionsDataProvider(): array
    {
        return [
            'umask 0022' => [0022, false, 0644],
            'umask 0027' => [0027, false, 0640],
            'umask 0022, stateful' => [0022, true, 0644],
            'umask 0027, stateful' => [0027, true, 0640],
        ];
    }

    /**
     * Renamed directories keep the execute bits needed to traverse them.
     *
     * @param int $umask
     * @param bool $stateful
     * @param int $expectedMode
     * @return void
     * @throws FileSystemException
     */
    #[DataProvider('renameDirectoryPermissionsDataProvider')]
    public function testRenameDirectoryAppliesDirectoryPermissions(
        int $umask,
        bool $stateful,
        int $expectedMode
    ): void {
        $driver = new File($stateful);
        $source = $this->generatedPath . '/source';
        $target = $this->generatedPath . '/target';
        $driver->createDirectory($source);
        $driver->filePutContents($source . '/file.txt', 'file content');
        $driver->changePermissions($source, 0700);

        $this->originalUmask = umask($umask);
        self::assertTrue($driver->rename($source, $target));

        self::assertFalse($driver->isExists($source));
        self::assertSame('file content', $driver->fileGetContents($target . '/file.txt'));
        clearstatcache(true, $target);
        self::assertSame(sprintf('%04o', $expectedMode), sprintf('%04o', fileperms($target) & 0777));
    }

    /**
     * @return array
     */
    public static function renameDirectoryPermissionsDataProvider(): array
    {
        return [
            'umask 0022' => [0022, false, 0755],
            'umask 0027' => [0027, false, 0750],
            'umask 0022, stateful' => [0022, true, 0755],
            'umask 0027, stateful' => [0027, true, 0750],
        ];
    }

    /**
     * Remove generated directories.
     *
     * @throws FileSystemException
     * @return void
     */
    private function removeGeneratedDirectory(): void
    {
        if (is_dir($this->generatedPath)) {
            $this->driver->deleteDirectory($this->generatedPath);
        }
    }
}
