<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Filesystem\Driver;

use Magento\Framework\Exception\FileSystemException;
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
