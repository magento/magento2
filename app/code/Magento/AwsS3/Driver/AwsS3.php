<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AwsS3\Driver;

use League\Flysystem\AwsS3v3\AwsS3Adapter;
use League\Flysystem\Config;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\DriverInterface;

/**
 * Driver for AWS S3 IO operations.
 */
class AwsS3 implements DriverInterface
{
    public const TYPE_DIR = 'dir';
    public const TYPE_FILE = 'file';

    private const CONFIG = ['ACL' => 'public-read'];

    /**
     * @var AwsS3Adapter
     */
    private $adapter;

    /**
     * @var array
     */
    private $streams = [];

    /**
     * @param AwsS3Adapter $adapter
     */
    public function __construct(AwsS3Adapter $adapter)
    {
        $this->adapter = $adapter;
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
        $path = $this->normalizeRelativePath($path);

        if (isset($this->streams[$path])) {
            //phpcs:disable
            return file_get_contents(stream_get_meta_data($this->streams[$path])['uri']);
            //phpcs:enable
        }

        return $this->adapter->read($path)['contents'] ?? '';
    }

    /**
     * @inheritDoc
     */
    public function isExists($path): bool
    {
        if ($path === '/') {
            return true;
        }

        $path = $this->normalizeRelativePath($path);

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

        return $this->createDirectoryRecursively(
            $this->normalizeRelativePath($path)
        );
    }

    /**
     * Created directory recursively.
     *
     * @param string $path
     * @return bool
     * @throws FileSystemException
     */
    private function createDirectoryRecursively(string $path): bool
    {
        //phpcs:ignore Magento2.Functions.DiscouragedFunction
        $parentDir = dirname($path);

        while (!$this->isDirectory($parentDir)) {
            $this->createDirectoryRecursively($parentDir);
        }

        return (bool)$this->adapter->createDir(rtrim($path, '/'), new Config([]));
    }

    /**
     * @inheritDoc
     */
    public function copy($source, $destination, DriverInterface $targetDriver = null): bool
    {
        return $this->adapter->copy(
            $this->normalizeRelativePath($source),
            $this->normalizeRelativePath($destination)
        );
    }

    /**
     * @inheritDoc
     */
    public function deleteFile($path): bool
    {
        return $this->adapter->delete(
            $this->normalizeRelativePath($path)
        );
    }

    /**
     * @inheritDoc
     */
    public function deleteDirectory($path): bool
    {
        return $this->adapter->deleteDir(
            $this->normalizeRelativePath($path)
        );
    }

    /**
     * @inheritDoc
     */
    public function filePutContents($path, $content, $mode = null, $context = null): int
    {
        $path = $this->normalizeRelativePath($path);

        return $this->adapter->write($path, $content, new Config(self::CONFIG))['size'];
    }

    /**
     * @inheritDoc
     */
    public function readDirectoryRecursively($path = null): array
    {
        return $this->adapter->listContents(
            $this->normalizeRelativePath($path),
            true
        );
    }

    /**
     * @inheritDoc
     */
    public function readDirectory($path): array
    {
        return $this->adapter->listContents(
            $this->normalizeRelativePath($path),
            false
        );
    }

    /**
     * @inheritDoc
     */
    public function getRealPathSafety($path)
    {
        return $this->normalizeAbsolutePath(
            $this->normalizeRelativePath($path)
        );
    }

    /**
     * @inheritDoc
     */
    public function getAbsolutePath($basePath, $path, $scheme = null)
    {
        if ($basePath && $path && 0 === strpos($path, $basePath)) {
            return $this->normalizeAbsolutePath(
                $this->normalizeRelativePath($path)
            );
        }

        if ($basePath && $basePath !== '/') {
            return $basePath . ltrim((string)$path, '/');
        }

        return $this->normalizeAbsolutePath($path);
    }

    /**
     * Resolves absolute path.
     *
     * @param string $path Relative path
     * @return string Absolute path
     */
    private function normalizeAbsolutePath(string $path = '.'): string
    {
        $path = ltrim($path, '/');

        if (!$path) {
            $path = '.';
        }

        return $this->adapter->getClient()->getObjectUrl(
            $this->adapter->getBucket(),
            $this->adapter->applyPathPrefix($path)
        );
    }

    /**
     * Resolves relative path.
     *
     * @param string $path Absolute path
     * @return string Relative path
     */
    private function normalizeRelativePath(string $path): string
    {
        return str_replace(
            $this->normalizeAbsolutePath(),
            '',
            $path
        );
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
        if (!$path || $path === '/') {
            return false;
        }

        $path = $this->normalizeRelativePath($path);
        $path = rtrim($path, '/');

        return $this->adapter->has($path) && $this->adapter->getMetadata($path)['type'] === self::TYPE_FILE;
    }

    /**
     * @inheritDoc
     */
    public function isDirectory($path): bool
    {
        if (in_array($path, ['.', '/'], true)) {
            return true;
        }

        $path = $this->normalizeRelativePath($path);

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
        $basePath = $this->normalizeAbsolutePath($basePath);
        $absolutePath = $this->normalizeAbsolutePath((string)$path);

        if ($basePath === $absolutePath . '/' || strpos($absolutePath, $basePath) === 0) {
            return ltrim(substr($absolutePath, strlen($basePath)), '/');
        }

        return ltrim($path, '/');
    }

    /**
     * @inheritDoc
     */
    public function getParentDirectory($path): string
    {
        //phpcs:ignore Magento2.Functions.DiscouragedFunction
        return dirname($this->normalizeAbsolutePath($path));
    }

    /**
     * @inheritDoc
     */
    public function getRealPath($path)
    {
        return $this->normalizeAbsolutePath($path);
    }

    /**
     * @inheritDoc
     */
    public function rename($oldPath, $newPath, DriverInterface $targetDriver = null): bool
    {
        return $this->adapter->rename(
            $this->normalizeRelativePath($oldPath),
            $this->normalizeRelativePath($newPath)
        );
    }

    /**
     * @inheritDoc
     */
    public function stat($path): array
    {
        $path = $this->normalizeRelativePath($path);
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
            'mimetype' => $metaInfo['mimetype']
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
        return true;
    }

    /**
     * @inheritDoc
     */
    public function touch($path, $modificationTime = null)
    {
        $path = $this->normalizeRelativePath($path);

        $content = $this->adapter->has($path) ?
            $this->adapter->read($path)['contents']
            : '';

        return (bool)$this->adapter->write($path, $content, new Config([]));
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
        //phpcs:enable

        foreach ($this->streams as $stream) {
            //phpcs:disable
            if (stream_get_meta_data($stream)['uri'] === $resourcePath) {
                return fwrite($stream, $data);
            }
            //phpcs:enable
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function fileClose($resource): bool
    {
        //phpcs:disable
        $resourcePath = stream_get_meta_data($resource)['uri'];
        //phpcs:enable

        foreach ($this->streams as $path => $stream) {
            //phpcs:disable
            if (stream_get_meta_data($stream)['uri'] === $resourcePath) {
                $this->adapter->writeStream($path, $resource, new Config(self::CONFIG));

                // Remove path from streams after
                unset($this->streams[$path]);

                return fclose($stream);
            }
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function fileOpen($path, $mode)
    {
        $path = $this->normalizeRelativePath($path);

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
