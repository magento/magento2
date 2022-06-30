<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AwsS3\Test\Mftf\Helper;

use Aws\S3\S3Client;
use Codeception\Lib\ModuleContainer;
use League\Flysystem\AwsS3V3\AwsS3V3Adapter;
use League\Flysystem\PathPrefixer;
use Magento\AwsS3\Driver\AwsS3;
use Magento\FunctionalTestingFramework\Helper\Helper;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\RemoteStorage\Driver\Adapter\MetadataProvider;

/**
 * Class for MFTF helpers for doing file assertions using S3.
 */
class S3FileAssertions extends Helper
{
    /**
     * @var DriverInterface $driver
     */
    private $driver;

    /**
     * Call the parent constructor then create the AwsS3 driver from environment variables
     *
     * @param ModuleContainer $moduleContainer
     * @param array|null $config
     * @return void
     */
    public function __construct(ModuleContainer $moduleContainer, ?array $config = null)
    {
        parent::__construct($moduleContainer, $config);

        $region = getenv('REMOTE_STORAGE_AWSS3_REGION');
        $prefix = getenv('REMOTE_STORAGE_AWSS3_PREFIX');
        $bucket = getenv('REMOTE_STORAGE_AWSS3_BUCKET');
        $accessKey = getenv('REMOTE_STORAGE_AWSS3_ACCESS_KEY');
        $secretKey = getenv('REMOTE_STORAGE_AWSS3_SECRET_KEY');

        $config = [
            'version' => 'latest',
            'credentials' => [
                'key' => $accessKey,
                'secret' => $secretKey
            ],
            'bucket' => $bucket,
            'region' => $region
        ];

        if (empty($config['credentials']['key']) || empty($config['credentials']['secret'])) {
            unset($config['credentials']);
        }

        $client = new S3Client($config);
        $adapter = new AwsS3V3Adapter($client, $config['bucket'], $prefix);
        $prefixer = new PathPrefixer($prefix);
        $objectUrl = $client->getObjectUrl($config['bucket'], ltrim($prefixer->prefixPath('.'), '/'));
        $metadataProvider = new MetadataProvider($adapter, new DummyMetadataCache());
        $s3Driver = new AwsS3($adapter, new MockTestLogger(), $objectUrl, $metadataProvider);

        $this->driver = $s3Driver;
    }

    /**
     * Create a file in the S3 bucket
     *
     * @param string $filePath
     * @param string $text
     * @return void
     *
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function createTextFile($filePath, $text): void
    {
        $this->driver->filePutContents($filePath, $text);
    }

    /**
     * Delete a file from the S3 bucket if it exists
     *
     * @param string $filePath
     * @return void
     *
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function deleteFileIfExists($filePath): void
    {
        if ($this->driver->isExists($filePath)) {
            $this->driver->deleteFile($filePath);
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
        $this->driver->copy($source, $destination);
    }

    /**
     * Copy file from the local source into AWS S3 $destination
     *
     * @param string $source local FS path to the file which should be copied
     * @param string $destination path on AWS S3 where the file should be paste
     * @return void
     *
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function copyFromLocal($source, $destination): void
    {
        $this->driver->filePutContents(
            $destination,
            file_get_contents((substr($source, 0, 1) === '/') ? $source : MAGENTO_BP . '/' . $source)
        );
    }

    /**
     * Create directory in the S3 bucket
     *
     * @param string $path
     * @param int $permissions
     * @return void
     *
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function createDirectory($path, $permissions = 0777): void
    {
        $this->driver->createDirectory($path, $permissions);
    }

    /**
     * Recursive delete directory in the S3 bucket
     *
     * @param string $path
     * @return void
     *
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function deleteDirectory($path): void
    {
        if ($this->driver->isDirectory($path)) {
            $this->driver->deleteDirectory($path);
        }
    }

    /**
     * Assert a file exists on the remote storage system
     *
     * @param string $filePath
     * @param string $message
     * @return void
     *
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function assertFileExists($filePath, $message = ''): void
    {
        $this->assertTrue($this->driver->isExists($filePath), "Failed asserting $filePath exists. " . $message);
    }

    /**
     * Asserts that a file with the given glob pattern exists in the given path on the remote storage system
     *
     * @param string $path
     * @param string $pattern
     * @param string $message
     *
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function assertGlobbedFileExists($path, $pattern, $message = ''): void
    {
        $files = $this->driver->search($pattern, $path);
        $this->assertNotEmpty(
            $files,
            "Failed asserting file matching glob pattern \"$pattern\" at location \"$path\" is not empty. " . $message
        );
    }

    /**
     * Asserts that a directory exists on the remote storage system
     *
     * @param string $path
     * @param string $message
     * @return void
     *
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function assertDirectoryExists($path, $message = ''): void
    {
        $this->assertTrue($this->driver->isDirectory($path), "Failed asserting $path exists. " . $message);
    }

    /**
     * Asserts that a directory does not exist on the remote storage system
     *
     * @param string $path
     * @param string $message
     * @return void
     *
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function assertDirectoryDoesNotExist($path, $message = ''): void
    {
        $this->assertFalse($this->driver->isDirectory($path), "Failed asserting $path does not exist. " . $message);
    }

    /**
     * Assert a file does not exist on the remote storage system
     *
     * @param string $filePath
     * @param string $message
     * @return void
     *
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function assertFileDoesNotExist($filePath, $message = ''): void
    {
        $this->assertFalse($this->driver->isExists($filePath), $message);
    }

    /**
     * Assert a file on the remote storage system has no contents
     *
     * @param string $filePath
     * @param string $message
     * @return void
     *
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function assertFileEmpty($filePath, $message = ''): void
    {
        $this->assertEmpty(
            $this->driver->fileGetContents($filePath),
            "Failed asserting $filePath is empty. " . $message
        );
    }

    /**
     * Assert a file on the remote storage system is not empty
     *
     * @param string $filePath
     * @param string $message
     * @return void
     *
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function assertFileNotEmpty($filePath, $message = ''): void
    {
        $this->assertNotEmpty(
            $this->driver->fileGetContents($filePath),
            "Failed asserting $filePath is not empty. " . $message
        );
    }

    /**
     * Assert a file on the remote storage system contains a given string
     *
     * @param string $filePath
     * @param string $text
     * @param string $message
     * @return void
     *
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function assertFileContainsString($filePath, $text, $message = ''): void
    {
        $this->assertStringContainsString(
            $text,
            $this->driver->fileGetContents($filePath),
            "Failed asserting $filePath contains $text. " . $message
        );
    }

    /**
     * Asserts that a file with the given glob pattern at the given path
     * on the remote storage system contains a given string
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
    public function assertGlobbedFileContainsString($path, $pattern, $text, $fileIndex = 0, $message = ''): void
    {
        $files = $this->driver->search($pattern, $path);
        $this->assertStringContainsString(
            $text,
            $this->driver->fileGetContents($files[$fileIndex] ?? ''),
            "Failed asserting file of index \"$fileIndex\" matching glob pattern \"$pattern\""
            . " at location \"$path\" contains $text. " . $message
        );
    }

    /**
     * Assert a file on the remote storage system does not contain a given string
     *
     * @param string $filePath
     * @param string $text
     * @param string $message
     * @return void
     *
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function assertFileDoesNotContainString($filePath, $text, $message = ''): void
    {
        $this->assertStringNotContainsString(
            $text,
            $this->driver->fileGetContents($filePath),
            "Failed asserting $filePath does not contain $text. " . $message
        );
    }

    /**
     * Asserts that a directory on the remote storage system is empty
     *
     * @param string $path
     * @param string $message
     * @return void
     *
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function assertDirectoryEmpty($path, $message = ''): void
    {
        $this->assertEmpty($this->driver->readDirectory($path), "Failed asserting $path is empty. " . $message);
    }

    /**
     * Asserts that a directory on the remote storage system is not empty
     *
     * @param string $path
     * @param string $message
     * @return void
     *
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function assertDirectoryNotEmpty($path, $message = ''): void
    {
        $this->assertNotEmpty($this->driver->readDirectory($path), "Failed asserting $path is not empty. " . $message);
    }
}
