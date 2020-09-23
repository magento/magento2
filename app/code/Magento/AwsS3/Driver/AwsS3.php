<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AwsS3\Driver;

use Aws\S3\S3Client;
use League\Flysystem\AwsS3v3\AwsS3Adapter;
use League\Flysystem\Config;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\DriverInterface;

/**
 * Driver for AWS S3 IO operations.
 */
class AwsS3 implements DriverInterface
{
    public const S3 = 'aws-s3';

    private const TYPE_DIR = 'dir';
    private const TYPE_FILE = 'file';

    /**
     * @var AwsS3Adapter
     */
    private $adapter;

    /**
     * @var array
     */
    private $streams = [];

    /**
     * @param string $region
     * @param string $bucket
     * @param string|null $key
     * @param string|null $secret
     */
    public function __construct(string $region, string $bucket, string $key = null, string $secret = null)
    {
        $config = [
            'region' => $region,
            'version' => 'latest'
        ];

        if ($key && $secret) {
            $config['credentials'] = [
                'key' => $key,
                'secret' => $secret,
            ];
        }

        $client = new S3Client($config);
        $this->adapter = new AwsS3Adapter($client, $bucket);
    }

    /**
     * Destroy opened streams.
     *
     * @throws FileSystemException
     */
    public function __destruct()
    {
        foreach ($this->streams as $stream) {
            $this->fileClose($stream);
        }
    }

    /**
     * @inheritDoc
     */
    public function fileGetContents($path, $flag = null, $context = null): string
    {
        $path = $this->getRelativePath('', $path);

        if (isset($this->streams[$path])) {
            //phpcs:disable
            return file_get_contents(stream_get_meta_data($this->streams[$path])['uri']);
            //phpcs:enable
        }

        return $this->adapter->read($path)['contents'];
    }

    /**
     * @inheritDoc
     */
    public function isExists($path): bool
    {
        if ($path === '/') {
            return true;
        }

        $path = $this->getRelativePath('', $path);

        if (!$path || $path === '/') {
            return true;
        }

        return $this->adapter->has($path);
    }

    /**
     * @inheritDoc
     */
    public function isWritable($path): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function createDirectory($path, $permissions = 0777): bool
    {
        if ($path === '/') {
            return true;
        }

        $path = $this->getRelativePath('', $path);

        return (bool)$this->adapter->createDir(rtrim($path, '/'), new Config([]));
    }

    /**
     * @inheritDoc
     */
    public function copy($source, $destination, DriverInterface $targetDriver = null): bool
    {
        $source = $this->getRelativePath('', $source);
        $destination = $this->getRelativePath('', $destination);

        return $this->adapter->copy($source, $destination);
    }

    /**
     * @inheritDoc
     */
    public function deleteFile($path): bool
    {
        $path = $this->getRelativePath('', $path);

        return $this->adapter->delete($path);
    }

    /**
     * @inheritDoc
     */
    public function deleteDirectory($path): bool
    {
        $path = $this->getRelativePath('', $path);

        return $this->adapter->deleteDir($path);
    }

    /**
     * @inheritDoc
     */
    public function filePutContents($path, $content, $mode = null, $context = null): int
    {
        $path = $this->getRelativePath('', $path);

        return $this->adapter->write($path, $content, new Config(['ACL' => 'public-read']))['size'];
    }

    /**
     * @inheritDoc
     */
    public function readDirectoryRecursively($path = null): array
    {
        $path = $this->getRelativePath('', $path);

        return $this->adapter->listContents($path, true);
    }

    /**
     * @inheritDoc
     */
    public function readDirectory($path): array
    {
        $path = $this->getRelativePath('', $path);

        return $this->adapter->listContents($path, false);
    }

    /**
     * @inheritDoc
     */
    public function getRealPathSafety($path)
    {
        return '/';
    }

    /**
     * @inheritDoc
     */
    public function getAbsolutePath($basePath, $path, $scheme = null)
    {
        $path = $this->getRelativePath($basePath, $path);

        if ($path === '/') {
            $path = '';
        }

        if ($basePath !== '/') {
            $path = $basePath . $path;
        }

        $path = $path ?: '.';

        return $this->adapter->getClient()->getObjectUrl($this->adapter->getBucket(), $path);
    }

    /**
     * @inheritDoc
     */
    public function isReadable($path): bool
    {
        return $this->isExists($path);
    }

    /**
     * @inheritDoc
     */
    public function isFile($path): bool
    {
        if ($path === '/') {
            return false;
        }

        $path = $this->getRelativePath('', $path);
        $path = rtrim($path, '/');

        return $this->adapter->has($path) && $this->adapter->getMetadata($path)['type'] === self::TYPE_FILE;
    }

    /**
     * @inheritDoc
     */
    public function isDirectory($path): bool
    {
        if ($path === '/') {
            return true;
        }

        $path = $this->getRelativePath('', $path);

        if (!$path || $path === '/') {
            return true;
        }

        $path = rtrim($path, '/') . '/';

        return $this->adapter->has($path) && $this->adapter->getMetadata($path)['type'] === self::TYPE_DIR;
    }

    /**
     * @inheritDoc
     */
    public function getRelativePath($basePath, $path = null): string
    {
        $relativePath = str_replace(
            $this->adapter->getClient()->getObjectUrl($this->adapter->getBucket(), '.'),
            '',
            $path
        );

        if ($basePath && $basePath !== '/') {
            $relativePath = str_replace($basePath, '', $relativePath);
        }

        $relativePath = ltrim($relativePath, '/');

        if (!$relativePath) {
            $relativePath = '/';
        }

        return $relativePath;
    }

    /**
     * @inheritDoc
     */
    public function getParentDirectory($path): string
    {
        return '/';
    }

    /**
     * @inheritDoc
     */
    public function getRealPath($path)
    {
        return $this->getAbsolutePath('', $path);
    }

    /**
     * @inheritDoc
     */
    public function rename($oldPath, $newPath, DriverInterface $targetDriver = null): bool
    {
        $oldPath = $this->getRelativePath('', $oldPath);
        $newPath = $this->getRelativePath('', $newPath);

        return $this->adapter->rename($oldPath, $newPath);
    }

    /**
     * @inheritDoc
     */
    public function stat($path): array
    {
        $path = $this->getRelativePath('', $path);
        $metaInfo = $this->adapter->getMetadata($path);

        if (!$metaInfo) {
            throw new FileSystemException(__('Cannot gather stats! %1', (array)$path));
        }

        return [
            'dev' => 0,
            'ino' => 0,
            'mode' => 0,
            'nlink' => 0,
            'uid' => 0,
            'gid' => 0,
            'rdev' => 0,
            'atime' => 0,
            'ctime' => 0,
            'blksize' => 0,
            'blocks' => 0,
            'size' => $metaInfo['size'],
            'type' => $metaInfo['type'],
            'mtime' => $metaInfo['timestamp'],
            'disposition' => null,
        ];
    }

    /**
     * @inheritDoc
     */
    public function search($pattern, $path): array
    {
        throw new FileSystemException(__('Method %1 is not supported', __METHOD__));
    }

    /**
     * @inheritDoc
     */
    public function symlink($source, $destination, DriverInterface $targetDriver = null): bool
    {
        throw new FileSystemException(__('Method %1 is not supported', __METHOD__));
    }

    /**
     * @inheritDoc
     */
    public function changePermissions($path, $permissions): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function changePermissionsRecursively($path, $dirPermissions, $filePermissions): bool
    {
        throw new FileSystemException(__('Method %1 is not supported', __METHOD__));
    }

    /**
     * @inheritDoc
     */
    public function touch($path, $modificationTime = null)
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function fileReadLine($resource, $length, $ending = null): string
    {
        throw new FileSystemException(__('Method %1 is not supported', __METHOD__));
    }

    /**
     * @inheritDoc
     */
    public function fileRead($resource, $length): string
    {
        //phpcs:ignore Magento2.Functions.DiscouragedFunction
        $result = fread($resource, $length);
        if ($result === false) {
            throw new FileSystemException(__('File cannot be read %1', [$this->getWarningMessage()]));
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function fileGetCsv($resource, $length = 0, $delimiter = ',', $enclosure = '"', $escape = '\\')
    {
        //phpcs:disable
        $metadata = stream_get_meta_data($resource);
        //phpcs:enable
        $file = $this->adapter->read($metadata['uri'])['contents'];

        return str_getcsv($file, $delimiter, $enclosure, $escape);
    }

    /**
     * @inheritDoc
     */
    public function fileTell($resource): int
    {
        throw new FileSystemException(__('Method %1 is not supported', __METHOD__));
    }

    /**
     * @inheritDoc
     */
    public function fileSeek($resource, $offset, $whence = SEEK_SET): int
    {
        throw new FileSystemException(__('Method %1 is not supported', __METHOD__));
    }

    /**
     * @inheritDoc
     */
    public function endOfFile($resource): bool
    {
        throw new FileSystemException(__('Method %1 is not supported', __METHOD__));
    }

    /**
     * @inheritDoc
     */
    public function filePutCsv($resource, array $data, $delimiter = ',', $enclosure = '"')
    {
        //phpcs:ignore Magento2.Functions.DiscouragedFunction
        return fputcsv($resource, $data, $delimiter, $enclosure);
    }

    /**
     * @inheritDoc
     */
    public function fileFlush($resource): bool
    {
        throw new FileSystemException(__('Method %1 is not supported', __METHOD__));
    }

    /**
     * @inheritDoc
     */
    public function fileLock($resource, $lockMode = LOCK_EX): bool
    {
        throw new FileSystemException(__('Method %1 is not supported', __METHOD__));
    }

    /**
     * @inheritDoc
     */
    public function fileUnlock($resource): bool
    {
        throw new FileSystemException(__('Method %1 is not supported', __METHOD__));
    }

    /**
     * @inheritDoc
     */
    public function fileWrite($resource, $data)
    {
        //phpcs:disable
        $resourcePath = stream_get_meta_data($resource)['uri'];

        foreach ($this->streams as $stream) {
            if (stream_get_meta_data($stream)['uri'] === $resourcePath) {
                return fwrite($stream, $data);
            }
        }
        //phpcs:enable

        return false;
    }

    /**
     * @inheritDoc
     */
    public function fileClose($resource): bool
    {
        //phpcs:disable
        $resourcePath = stream_get_meta_data($resource)['uri'];

        foreach ($this->streams as $path => $stream) {
            if (stream_get_meta_data($stream)['uri'] === $resourcePath) {
                $this->adapter->writeStream($path, $resource, new Config(['ACL' => 'public-read']));

                // Remove path from streams after
                unset($this->streams[$path]);

                return fclose($stream);
            }
        }
        //phpcs:enable

        return false;
    }

    /**
     * @inheritDoc
     */
    public function fileOpen($path, $mode)
    {
        $path = $this->getRelativePath('', $path);

        if (!isset($this->streams[$path])) {
            $this->streams[$path] = tmpfile();
            if ($this->adapter->has($path)) {
                $file = tmpfile();
                //phpcs:ignore Magento2.Functions.DiscouragedFunction
                fwrite($file, $this->adapter->read($path)['contents']);
                //phpcs:ignore Magento2.Functions.DiscouragedFunction
                fseek($file, 0);
            } else {
                $file = tmpfile();
            }
            $this->streams[$path] = $file;
        }

        return $this->streams[$path];
    }

    /**
     * Returns last warning message string
     *
     * @return string|null
     */
    private function getWarningMessage(): ?string
    {
        $warning = error_get_last();
        if ($warning && $warning['type'] === E_WARNING) {
            return 'Warning!' . $warning['message'];
        }

        return null;
    }
}
