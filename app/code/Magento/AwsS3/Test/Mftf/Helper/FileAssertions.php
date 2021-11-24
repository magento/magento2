<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AwsS3\Test\Mftf\Helper;

use Codeception\Lib\ModuleContainer;
use Magento\Catalog\Test\Mftf\Helper\LocalFileAssertions;
use Magento\FunctionalTestingFramework\Helper\Helper;

/**
 * File assertions proxy.
 *
 * Accepts plain file paths or json structure with paths by storage type in case there are different storage types.
 *
 * Sample structure:
 * {
 *  "local":"path/to/file.txt",
 *  "s3":"custom_path/s3_specific/file.txt"
 * }
 *
 * Storage type driver is identified by ENV variable 'MEDIA_STORAGE_DRIVER'.
 * Use 'MEDIA_STORAGE_DRIVER=local' for running tests against local filesystem.
 * Use 'MEDIA_STORAGE_DRIVER=s3' for running tests against AWS S3 filesystem.
 */
class FileAssertions extends Helper implements FileAssertionsInterface
{
    /**
     * Filesystem types.
     */
    private const STORAGE_TYPE_LOCAL = 'local';
    private const STORAGE_TYPE_S3 = 's3';

    /**
     * @var FileAssertionsInterface
     */
    private $helperInstance;

    /**
     * Storage type.
     *
     * @var string
     */
    private $storageType;

    /**
     * Call the parent constructor then create the driver from environment variables
     *
     * @param ModuleContainer $moduleContainer
     * @param array|null $config
     * @return void
     */
    public function __construct(ModuleContainer $moduleContainer, ?array $config = null)
    {
        parent::__construct($moduleContainer, $config);
        $this->storageType = getenv('MEDIA_STORAGE_DRIVER') ?: self::STORAGE_TYPE_LOCAL;
        if ($this->storageType === self::STORAGE_TYPE_S3) {
            $this->helperInstance = new S3FileAssertions($moduleContainer, $config);
        } else {
            $this->helperInstance = new LocalFileAssertions($moduleContainer, $config);
        }
    }

    /**
     * Create a file in the storage.
     *
     * @param string $filePath - path to file or json structure with paths by storage type.
     * @param string $text
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function createTextFile($filePath, $text): void
    {
        $this->helperInstance->createTextFile($this->extractFilePath($filePath), $text);
    }

    /**
     * Delete text file if exists.
     *
     * @param string $filePath - path to file or json structure with paths by storage type.
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function deleteFileIfExists($filePath): void
    {
        $this->helperInstance->deleteFileIfExists($this->extractFilePath($filePath));
    }

    /**
     * Copy source file to destination folder.
     *
     * @param string $source - path to file or json structure with paths by storage type.
     * @param string $destination - path to file or json structure with paths by storage type.
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function copy($source, $destination): void
    {
        $this->helperInstance->copy($this->extractFilePath($source), $this->extractFilePath($destination));
    }

    /**
     * Create directory.
     *
     * @param string $path - path to file or json structure with paths by storage type.
     * @param int $permissions
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function createDirectory($path, $permissions = 0777): void
    {
        $this->helperInstance->createDirectory($this->extractFilePath($path), $permissions);
    }

    /**
     * Delete directory.
     *
     * @param string $path - path to file or json structure with paths by storage type.
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function deleteDirectory($path): void
    {
        $this->helperInstance->deleteDirectory($this->extractFilePath($path));
    }

    /**
     * Assert file exists by path.
     *
     * @param string $filePath - path to file or json structure with paths by storage type.
     * @param string $message
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function assertFileExists($filePath, $message = ''): void
    {
        $this->helperInstance->assertFileExists($this->extractFilePath($filePath), $message);
    }

    /**
     * Assert file exists in glob results obtained by pattern.
     *
     * @param string $path - path to file or json structure with paths by storage type.
     * @param string $pattern
     * @param string $message
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function assertGlobbedFileExists($path, $pattern, $message = ''): void
    {
        $this->helperInstance->assertGlobbedFileExists($this->extractFilePath($path), $pattern, $message);
    }

    /**
     * Assert directory exists.
     *
     * @param string $path - path to file or json structure with paths by storage type.
     * @param string $message
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function assertDirectoryExists($path, $message = ''): void
    {
        $this->helperInstance->assertDirectoryExists($this->extractFilePath($path), $message);
    }

    /**
     * Assert directory does not exist.
     *
     * @param string $path - path to file or json structure with paths by storage type.
     * @param string $message
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function assertDirectoryDoesNotExist($path, $message = ''): void
    {
        $this->helperInstance->assertDirectoryDoesNotExist($this->extractFilePath($path), $message);
    }

    /**
     * Assert file does not exist.
     *
     * @param string $filePath - path to file or json structure with paths by storage type.
     * @param string $message
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function assertFileDoesNotExist($filePath, $message = ''): void
    {
        $this->helperInstance->assertFileDoesNotExist($this->extractFilePath($filePath), $message);
    }

    /**
     * Assert file exists and is empty.
     *
     * @param string $filePath - path to file or json structure with paths by storage type.
     * @param string $message
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function assertFileEmpty($filePath, $message = ''): void
    {
        $this->helperInstance->assertFileEmpty($this->extractFilePath($filePath), $message);
    }

    /**
     * Assert file exists and is not empty.
     *
     * @param string $filePath - path to file or json structure with paths by storage type.
     * @param string $message
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function assertFileNotEmpty($filePath, $message = ''): void
    {
        $this->helperInstance->assertFileNotEmpty($this->extractFilePath($filePath), $message);
    }

    /**
     * Assert file contains given string.
     *
     * @param string $filePath - path to file or json structure with paths by storage type.
     * @param string $text
     * @param string $message
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function assertFileContainsString($filePath, $text, $message = ''): void
    {
        $this->helperInstance->assertFileContainsString($this->extractFilePath($filePath), $text, $message);
    }

    /**
     * Assert file obtained by glob pattern exists and contains string.
     *
     * @param string $path - path to file or json structure with paths by storage type.
     * @param string $pattern
     * @param string $text
     * @param int $fileIndex
     * @param string $message
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function assertGlobbedFileContainsString($path, $pattern, $text, $fileIndex = 0, $message = ''): void
    {
        $this->helperInstance->assertGlobbedFileContainsString(
            $this->extractFilePath($path),
            $pattern,
            $text,
            $fileIndex,
            $message
        );
    }

    /**
     * Assert file exists and does not contain given string.
     *
     * @param string $filePath - path to file or json structure with paths by storage type.
     * @param string $text
     * @param string $message
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function assertFileDoesNotContainString($filePath, $text, $message = ''): void
    {
        $this->helperInstance->assertFileDoesNotContainString($this->extractFilePath($filePath), $text, $message);
    }

    /**
     * Assert directory is empty.
     *
     * @param string $path - path to file or json structure with paths by storage type.
     * @param string $message
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function assertDirectoryEmpty($path, $message = ''): void
    {
        $this->helperInstance->assertDirectoryEmpty($this->extractFilePath($path), $message);
    }

    /**
     * Assert directory is not empty.
     *
     * @param string $path - path to file or json structure with paths by storage type.
     * @param string $message
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function assertDirectoryNotEmpty($path, $message = ''): void
    {
        $this->helperInstance->assertDirectoryNotEmpty($this->extractFilePath($path), $message);
    }

    /**
     * Extract file path from json string relevant for current storage type.
     * Returns given argument unchanged if no file path for current storage or given value is not a json structure.
     *
     * @param string $filePathJson - path to file or json structure with paths by storage type.
     * @return mixed
     */
    private function extractFilePath($filePathJson)
    {
        $filePathArgs = json_decode($filePathJson, true);
        if (isset($filePathArgs[$this->storageType])) {
            return $filePathArgs[$this->storageType];
        }
        return $filePathJson;
    }
}
