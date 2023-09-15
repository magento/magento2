<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AwsS3\Test\Mftf\Helper;

/**
 * Interface for MFTF helpers for doing file assertions.
 */
interface FileAssertionsInterface
{
    /**
     * Create a file in the storage
     *
     * @param string $filePath
     * @param string $text
     * @return void
     *
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function createTextFile($filePath, $text): void;

    /**
     * Delete a file from the storage if it exists
     *
     * @param string $filePath
     * @return void
     *
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function deleteFileIfExists($filePath): void;

    /**
     * Copy source into destination
     *
     * @param string $source
     * @param string $destination
     * @return void
     *
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function copy($source, $destination): void;

    /**
     * Copy file from the local source into local or remote destination depends on storage FS
     *
     * @param string $source
     * @param string $destination
     * @return void
     *
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function copyFromLocal($source, $destination): void;

    /**
     * Create directory in the storage
     *
     * @param string $path
     * @param int $permissions
     * @return void
     *
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function createDirectory($path, $permissions = 0777): void;

    /**
     * Recursive delete directory in the storage
     *
     * @param string $path
     * @return void
     *
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function deleteDirectory($path): void;

    /**
     * Assert a file exists in the storage
     *
     * @param string $filePath
     * @param string $message
     * @return void
     *
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function assertFileExists($filePath, $message = ''): void;

    /**
     * Asserts that a file with the given glob pattern exists in the given path in the storage
     *
     * @param string $path
     * @param string $pattern
     * @param string $message
     * @return void
     *
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function assertGlobbedFileExists($path, $pattern, $message = ''): void;

    /**
     * Asserts that a directory exists in the storage
     *
     * @param string $path
     * @param string $message
     * @return void
     *
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function assertDirectoryExists($path, $message = ''): void;

    /**
     * Asserts that a directory does not exist in the storage
     *
     * @param string $path
     * @param string $message
     * @return void
     *
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function assertDirectoryDoesNotExist($path, $message = ''): void;

    /**
     * Assert a file does not exist in the storage
     *
     * @param string $filePath
     * @param string $message
     * @return void
     *
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function assertFileDoesNotExist($filePath, $message = ''): void;

    /**
     * Assert a file in the storage has no contents
     *
     * @param string $filePath
     * @param string $message
     * @return void
     *
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function assertFileEmpty($filePath, $message = ''): void;

    /**
     * Assert a file in the storage is not empty
     *
     * @param string $filePath
     * @param string $message
     * @return void
     *
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function assertFileNotEmpty($filePath, $message = ''): void;

    /**
     * Assert a file in the storage contains a given string
     *
     * @param string $filePath
     * @param string $text
     * @param string $message
     * @return void
     *
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function assertFileContainsString($filePath, $text, $message = ''): void;

    /**
     * Asserts that a file with the given glob pattern at the given path in the storage contains a given string
     *
     * @param string $path
     * @param string $pattern
     * @param string $text
     * @param int $fileIndex
     * @param string $message
     * @return void
     *
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function assertGlobbedFileContainsString($path, $pattern, $text, $fileIndex = 0, $message = ''): void;

    /**
     * Assert a file in the storage does not contain a given string
     *
     * @param string $filePath
     * @param string $text
     * @param string $message
     * @return void
     *
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function assertFileDoesNotContainString($filePath, $text, $message = ''): void;

    /**
     * Asserts that a directory in the storage is empty
     *
     * @param string $path
     * @param string $message
     * @return void
     *
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function assertDirectoryEmpty($path, $message = ''): void;

    /**
     * Asserts that a directory in the storage is not empty
     *
     * @param string $path
     * @param string $message
     * @return void
     *
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function assertDirectoryNotEmpty($path, $message = ''): void;
}
