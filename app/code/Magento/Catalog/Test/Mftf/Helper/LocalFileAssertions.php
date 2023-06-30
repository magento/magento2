<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Mftf\Helper;

use Codeception\Lib\ModuleContainer;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\FunctionalTestingFramework\Helper\Helper;

/**
 * Class for MFTF helpers for doing file assertions using the local filesystem.
 *
 * If relative file paths are given assume they're in context of the Magento base path.
 */
class LocalFileAssertions extends Helper
{
    /**
     * @var DriverInterface $driver
     */
    private $driver;

    /**
     * Call the parent constructor then create the local filesystem driver
     *
     * @param ModuleContainer $moduleContainer
     * @param array|null $config
     * @return void
     */
    public function __construct(ModuleContainer $moduleContainer, ?array $config = null)
    {
        parent::__construct($moduleContainer, $config);

        $this->driver = new File();
    }

    /**
     * Create a text file
     *
     * @param string $filePath
     * @param string $text
     * @return void
     *
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function createTextFile($filePath, $text): void
    {
        $realPath = $this->expandPath($filePath);
        $this->driver->filePutContents($realPath, $text);
    }

    /**
     * Delete a file if it exists
     *
     * @param string $filePath
     * @return void
     *
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function deleteFileIfExists($filePath): void
    {
        $realPath = $this->expandPath($filePath);
        if ($this->driver->isExists($realPath)) {
            $this->driver->deleteFile($realPath);
        }
    }

    /**
     * Recursive delete directory
     *
     * @param string $path
     * @return void
     *
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function deleteDirectory($path): void
    {
        $realPath = $this->expandPath($path);
        if ($this->driver->isExists($realPath)) {
            $this->driver->deleteDirectory($realPath);
        }
    }

    /**
     * Copy source into destination
     *
     * @param string $source
     * @param string $destination
     * @return void
     *
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function copy($source, $destination): void
    {
        $sourceRealPath = $this->expandPath($source);
        $destinationRealPath = $this->expandPath($destination);
        $this->driver->copy($sourceRealPath, $destinationRealPath);
    }

    /**
     * Create directory
     *
     * @param string $path
     * @param int $permissions
     * @return void
     *
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function createDirectory($path, $permissions = 0777): void
    {
        $permissions = $this->convertOctalStringToDecimalInt($permissions);
        $sourceRealPath = $this->expandPath($path);
        $oldUmask = umask(0);
        $this->driver->createDirectory($sourceRealPath, $permissions);
        umask($oldUmask);
    }

    /**
     * Assert a file exists
     *
     * @param string $filePath
     * @param string $message
     * @return void
     *
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function assertFileExists($filePath, $message = ''): void
    {
        $realPath = $this->expandPath($filePath);
        $this->assertTrue($this->driver->isExists($realPath), $message);
    }

    /**
     * Assert a file does not exist
     *
     * @param string $filePath
     * @param string $message
     * @return void
     *
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function assertFileDoesNotExist($filePath, $message = ''): void
    {
        $realPath = $this->expandPath($filePath);
        $this->assertFalse($this->driver->isExists($realPath), $message);
    }

    /**
     * Assert a file has no contents
     *
     * @param string $filePath
     * @param string $message
     * @return void
     *
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function assertFileEmpty($filePath, $message = ""): void
    {
        $realPath = $this->expandPath($filePath);
        $this->assertEmpty($this->driver->fileGetContents($realPath), $message);
    }

    /**
     * Assert a file is not empty
     *
     * @param string $filePath
     * @param string $message
     * @return void
     *
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function assertFileNotEmpty($filePath, $message = ""): void
    {
        $realPath = $this->expandPath($filePath);
        $this->assertNotEmpty($this->driver->fileGetContents($realPath), $message);
    }

    /**
     * Assert a file contains a given string
     *
     * @param string $filePath
     * @param string $text
     * @param string $message
     * @return void
     *
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function assertFileContainsString($filePath, $text, $message = ""): void
    {
        $realPath = $this->expandPath($filePath);
        $this->assertStringContainsString($text, $this->driver->fileGetContents($realPath), $message);
    }

    /**
     * Assert a file does not contain a given string
     *
     * @param string $filePath
     * @param string $text
     * @param string $message
     * @return void
     *
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function assertFileDoesNotContain($filePath, $text, $message = ""): void
    {
        $realPath = $this->expandPath($filePath);
        $this->assertStringNotContainsString($text, $this->driver->fileGetContents($realPath), $message);
    }

    /**
     * Asserts that a directory is empty
     *
     * @param string $path
     * @param string $message
     * @return void
     *
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function assertDirectoryEmpty($path, $message = ""): void
    {
        $realPath = $this->expandPath($path);
        $this->assertEmpty($this->driver->readDirectory($realPath), $message);
    }

    /**
     * Helper function to convert an octal string to its decimal equivalent
     *
     * @param string $string
     * @return int
     *
     */
    private function convertOctalStringToDecimalInt($string): int
    {
        if (is_string($string)) {
            $string = octdec($string);
        }
        return $string;
    }

    /**
     * Helper function to construct the real path to the file
     *
     * If the given path isn't an absolute path then assume it's in context of the Magento root
     *
     * @param string $filePath
     * @return string
     */
    private function expandPath($filePath): string
    {
        return (substr($filePath, 0, 1) === '/') ? $filePath : MAGENTO_BP . '/' . $filePath;

    }
}
