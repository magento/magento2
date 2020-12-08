<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AwsS3\Driver;

use Generator;
use Exception;
use League\Flysystem\AdapterInterface;
use League\Flysystem\Config;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Phrase;
use Psr\Log\LoggerInterface;
use Magento\RemoteStorage\Driver\DriverException;
use Magento\RemoteStorage\Driver\RemoteDriverInterface;

/**
 * Driver for AWS S3 IO operations.
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class AwsS3 implements RemoteDriverInterface
{
    public const TYPE_DIR = 'dir';
    public const TYPE_FILE = 'file';

    private const TEST_FLAG = 'storage.flag';

    private const CONFIG = ['ACL' => 'private'];

    /**
     * @var AdapterInterface
     */
    private $adapter;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var array
     */
    private $streams = [];

    /**
     * @var string
     */
    private $objectUrl;

    /**
     * @param AdapterInterface $adapter
     * @param LoggerInterface $logger
     * @param string $objectUrl
     */
    public function __construct(
        AdapterInterface $adapter,
        LoggerInterface $logger,
        string $objectUrl
    ) {
        $this->adapter = $adapter;
        $this->logger = $logger;
        $this->objectUrl = $objectUrl;
    }

    /**
     * Destroy opened streams.
     */
    public function __destruct()
    {
        try {
            foreach ($this->streams as $stream) {
                $this->fileClose($stream);
            }
        } catch (\Exception $e) {
            // log exception as throwing an exception from a destructor causes a fatal error
            $this->logger->critical($e);
        }
    }

    /**
     * @inheritDoc
     */
    public function test(): void
    {
        try {
            $this->adapter->write(self::TEST_FLAG, '', new Config(self::CONFIG));
        } catch (Exception $exception) {
            throw new DriverException(__($exception->getMessage()), $exception);
        }
    }

    /**
     * @inheritDoc
     */
    public function fileGetContents($path, $flag = null, $context = null): string
    {
        $path = $this->normalizeRelativePath($path, true);

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

        $path = $this->normalizeRelativePath($path, true);

        if (!$path) {
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

        return $this->createDirectoryRecursively($path);
    }

    /**
     * Create directory recursively.
     *
     * @param string $path
     * @return bool
     * @throws FileSystemException
     */
    private function createDirectoryRecursively(string $path): bool
    {
        $path = $this->normalizeRelativePath($path);
        //phpcs:ignore Magento2.Functions.DiscouragedFunction
        $parentDir = dirname($path);

        while (!$this->isDirectory($parentDir)) {
            $this->createDirectoryRecursively($parentDir);
        }

        if (!$this->isDirectory($path)) {
            return (bool)$this->adapter->createDir(
                $this->fixPath($path),
                new Config(self::CONFIG)
            );
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function copy($source, $destination, DriverInterface $targetDriver = null): bool
    {
        return $this->adapter->copy(
            $this->normalizeRelativePath($source, true),
            $this->normalizeRelativePath($destination, true)
        );
    }

    /**
     * @inheritDoc
     */
    public function deleteFile($path): bool
    {
        return $this->adapter->delete(
            $this->normalizeRelativePath($path, true)
        );
    }

    /**
     * @inheritDoc
     */
    public function deleteDirectory($path): bool
    {
        return $this->adapter->deleteDir(
            $this->normalizeRelativePath($path, true)
        );
    }

    /**
     * @inheritDoc
     */
    public function filePutContents($path, $content, $mode = null): int
    {
        $path = $this->normalizeRelativePath($path, true);
        $config = self::CONFIG;

        if (false !== ($imageSize = @getimagesizefromstring($content))) {
            $config['Metadata'] = [
                'image-width' => $imageSize[0],
                'image-height' => $imageSize[1]
            ];
        }

        return $this->adapter->write($path, $content, new Config($config))['size'];
    }

    /**
     * @inheritDoc
     */
    public function readDirectoryRecursively($path = null): array
    {
        return $this->readPath($path, true);
    }

    /**
     * @inheritDoc
     */
    public function readDirectory($path): array
    {
        return $this->readPath($path, false);
    }

    /**
     * @inheritDoc
     */
    public function getRealPathSafety($path)
    {
        if (strpos($path, '/.') === false) {
            return $path;
        }

        $isAbsolute = strpos($path, $this->normalizeAbsolutePath('')) === 0;
        $path = $this->normalizeRelativePath($path);

        //Removing redundant directory separators.
        $path = preg_replace(
            '/\\/\\/+/',
            '/',
            $path
        );
        $pathParts = explode('/', $path);
        if (end($pathParts) === '.') {
            $pathParts[count($pathParts) - 1] = '';
        }
        $realPath = [];
        foreach ($pathParts as $pathPart) {
            if ($pathPart === '.') {
                continue;
            }
            if ($pathPart === '..') {
                array_pop($realPath);
                continue;
            }
            $realPath[] = $pathPart;
        }

        if ($isAbsolute) {
            return $this->normalizeAbsolutePath(implode('/', $realPath));
        }

        return implode('/', $realPath);
    }

    /**
     * @inheritDoc
     */
    public function getAbsolutePath($basePath, $path, $scheme = null)
    {
        $basePath = (string)$basePath;
        $path = (string)$path;

        if ($basePath && $path && 0 === strpos(rtrim($path, '/'), rtrim($basePath, '/'))) {
            return $this->normalizeAbsolutePath($path);
        }

        if ($basePath) {
            $path = $basePath . ltrim($path, '/');
        }

        return $this->normalizeAbsolutePath($path);
    }

    /**
     * Resolves relative path.
     *
     * @param string $path Absolute path
     * @param bool $fixPath
     * @return string Relative path
     */
    private function normalizeRelativePath(string $path, bool $fixPath = false): string
    {
        $relativePath = str_replace($this->normalizeAbsolutePath(''), '', $path);

        if ($fixPath) {
            $relativePath = $this->fixPath($relativePath);
        }

        return $relativePath;
    }

    /**
     * Resolves absolute path.
     *
     * @param string $path Relative path
     * @return string Absolute path
     */
    private function normalizeAbsolutePath(string $path): string
    {
        $path = str_replace($this->getObjectUrl(''), '', $path);

        return $this->getObjectUrl($path);
    }

    /**
     * Retrieves object URL from cache.
     *
     * @param string $path
     * @return string
     */
    private function getObjectUrl(string $path): string
    {
        return $this->objectUrl . ltrim($path, '/');
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

        $path = $this->normalizeRelativePath($path, true);

        if ($this->adapter->has($path) && ($meta = $this->adapter->getMetadata($path))) {
            return ($meta['type'] ?? null) === self::TYPE_FILE;
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function isDirectory($path): bool
    {
        if (in_array($path, ['.', '/', ''], true)) {
            return true;
        }

        $path = $this->normalizeRelativePath($path, true);

        if (!$path) {
            return true;
        }

        if ($this->adapter->has($path)) {
            $meta = $this->adapter->getMetadata($path);

            return !($meta && $meta['type'] === self::TYPE_FILE);
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function getRelativePath($basePath, $path = null): string
    {
        $basePath = (string)$basePath;
        $path = (string)$path;

        if (
            ($basePath && $path)
            && ($basePath === $path . '/' || strpos($path, $basePath) === 0)
        ) {
            $result = substr($path, strlen($basePath));
        } else {
            $result = $path;
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function getParentDirectory($path): string
    {
        //phpcs:ignore Magento2.Functions.DiscouragedFunction
        return rtrim(dirname($this->normalizeAbsolutePath($path)), '/') . '/';
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
            $this->normalizeRelativePath($oldPath, true),
            $this->normalizeRelativePath($newPath, true)
        );
    }

    /**
     * @inheritDoc
     */
    public function stat($path): array
    {
        $path = $this->normalizeRelativePath($path, true);
        $metaInfo = $this->adapter->getMetadata($path);

        if (!$metaInfo) {
            throw new FileSystemException(__('Cannot gather stats! %1', [$this->getWarningMessage()]));
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
            'size' => $metaInfo['size'] ?? 0,
            'type' => $metaInfo['type'] ?? '',
            'mtime' => $metaInfo['timestamp'] ?? 0,
            'disposition' => null
        ];
    }

    /**
     * @inheritDoc
     */
    public function getMetadata(string $path): array
    {
        $path = $this->normalizeRelativePath($path, true);
        $metaInfo = $this->adapter->getMetadata($path);

        if (!$metaInfo) {
            throw new FileSystemException(__('Cannot gather meta info! %1', [$this->getWarningMessage()]));
        }

        $mimeType = $this->adapter->getMimetype($path)['mimetype'];
        $size = $this->adapter->getSize($path)['size'];
        $timestamp = $this->adapter->getTimestamp($path)['timestamp'];

        return [
            'path' => $metaInfo['path'],
            'dirname' => $metaInfo['dirname'],
            'basename' => $metaInfo['basename'],
            'extension' => $metaInfo['extension'],
            'filename' => $metaInfo['filename'],
            'timestamp' => $timestamp,
            'size' => $size,
            'mimetype' => $mimeType,
            'extra' => [
                'image-width' => $metaInfo['metadata']['image-width'] ?? 0,
                'image-height' => $metaInfo['metadata']['image-height'] ?? 0
            ]
        ];
    }

    /**
     * @inheritDoc
     */
    public function search($pattern, $path): array
    {
        return iterator_to_array(
            $this->glob(rtrim($path, '/') . '/' . ltrim($pattern, '/')),
            false
        );
    }

    /**
     * Emulate php glob function for AWS S3 storage
     *
     * @param string $pattern
     * @return Generator
     * @throws FileSystemException
     */
    private function glob(string $pattern): Generator
    {
        $patternFound = preg_match('(\*|\?|\[.+\])', $pattern, $parentPattern, PREG_OFFSET_CAPTURE);

        if ($patternFound) {
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            $parentDirectory = dirname(substr($pattern, 0, $parentPattern[0][1] + 1));
            $leftover = substr($pattern, $parentPattern[0][1]);
            $index = strpos($leftover, '/');
            $searchPattern = $this->getSearchPattern($pattern, $parentPattern, $parentDirectory, $index);

            if ($this->isDirectory($parentDirectory)) {
                yield from $this->getDirectoryContent($parentDirectory, $searchPattern, $leftover, $index);
            }
        } elseif ($this->isExists($pattern)) {
            yield $this->normalizeAbsolutePath($pattern);
        }
    }

    /**
     * @inheritDoc
     */
    public function symlink($source, $destination, DriverInterface $targetDriver = null): bool
    {
        return $this->copy($source, $destination, $targetDriver);
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
    public function touch($path, $modificationTime = null): bool
    {
        $path = $this->normalizeRelativePath($path, true);

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
        // phpcs:disable
        $result = @stream_get_line($resource, $length, $ending);
        // phpcs:enable
        if (false === $result) {
            throw new FileSystemException(
                new Phrase('File cannot be read %1', [$this->getWarningMessage()])
            );
        }

        return $result;
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
        //phpcs:ignore Magento2.Functions.DiscouragedFunction
        $result = fgetcsv($resource, $length, $delimiter, $enclosure, $escape);
        if ($result === null) {
            throw new FileSystemException(
                new Phrase(
                    'The "%1" CSV handle is incorrect. Verify the handle and try again.',
                    [$this->getWarningMessage()]
                )
            );
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function fileTell($resource): int
    {
        $result = @ftell($resource);
        if ($result === null) {
            throw new FileSystemException(
                new Phrase('An error occurred during "%1" execution.', [$this->getWarningMessage()])
            );
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function fileSeek($resource, $offset, $whence = SEEK_SET): int
    {
        $result = @fseek($resource, $offset, $whence);
        if ($result === -1) {
            throw new FileSystemException(
                new Phrase(
                    'An error occurred during "%1" fileSeek execution.',
                    [$this->getWarningMessage()]
                )
            );
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function endOfFile($resource): bool
    {
        return feof($resource);
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
        $result = @fflush($resource);
        if (!$result) {
            throw new FileSystemException(
                new Phrase(
                    'An error occurred during "%1" fileFlush execution.',
                    [$this->getWarningMessage()]
                )
            );
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function fileLock($resource, $lockMode = LOCK_EX): bool
    {
        $result = @flock($resource, $lockMode);
        if (!$result) {
            throw new FileSystemException(
                new Phrase(
                    'An error occurred during "%1" fileLock execution.',
                    [$this->getWarningMessage()]
                )
            );
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function fileUnlock($resource): bool
    {
        $result = @flock($resource, LOCK_UN);
        if (!$result) {
            throw new FileSystemException(
                new Phrase(
                    'An error occurred during "%1" fileUnlock execution.',
                    [$this->getWarningMessage()]
                )
            );
        }

        return $result;
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
        $path = $this->normalizeRelativePath($path, true);

        if (!isset($this->streams[$path])) {
            $this->streams[$path] = tmpfile();
            if ($this->adapter->has($path)) {
                //phpcs:ignore Magento2.Functions.DiscouragedFunction
                fwrite($this->streams[$path], $this->adapter->read($path)['contents']);
                //phpcs:ignore Magento2.Functions.DiscouragedFunction
                rewind($this->streams[$path]);
            }
        }

        return $this->streams[$path];
    }

    /**
     * Removes slashes in path.
     *
     * @param string $path
     * @return string
     */
    private function fixPath(string $path): string
    {
        return trim($path, '/');
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

    /**
     * Read directory by path and is recursive flag
     *
     * @param string $path
     * @param bool $isRecursive
     * @return array
     */
    private function readPath(string $path, $isRecursive = false): array
    {
        $relativePath = $this->normalizeRelativePath($path);
        $contentsList = $this->adapter->listContents(
            $this->fixPath($relativePath),
            $isRecursive
        );

        $itemsList = [];
        foreach ($contentsList as $item) {
            if (isset($item['path'])
                && $item['path'] !== $relativePath
                && (!$relativePath || strpos($item['path'], $relativePath) === 0)) {
                $itemsList[] = $this->getAbsolutePath($item['dirname'], $item['path']);
            }
        }

        return $itemsList;
    }

    /**
     * Get search pattern for directory
     *
     * @param string $pattern
     * @param array $parentPattern
     * @param string $parentDirectory
     * @param int|bool $index
     * @return string
     */
    private function getSearchPattern(string $pattern, array $parentPattern, string $parentDirectory, $index): string
    {
        $parentLength = strlen($parentDirectory);
        if ($index !== false) {
            $searchPattern = substr(
                $pattern,
                $parentLength + 1,
                $parentPattern[0][1] - $parentLength + $index - 1
            );
        } else {
            $searchPattern = substr($pattern, $parentLength + 1);
        }

        $replacement = [
            '/\*/' => '.*',
            '/\?/' => '.',
            '/\//' => '\/'
        ];

        return preg_replace(array_keys($replacement), array_values($replacement), $searchPattern);
    }

    /**
     * Get directory content by given search pattern
     *
     * @param string $parentDirectory
     * @param string $searchPattern
     * @param string $leftover
     * @param int|bool $index
     * @return Generator
     * @throws FileSystemException
     */
    private function getDirectoryContent(
        string $parentDirectory,
        string $searchPattern,
        string $leftover,
        $index
    ): Generator {
        $items = $this->readDirectory($parentDirectory);
        $directoryContent = [];
        foreach ($items as $item) {
            if (preg_match('/' . $searchPattern . '$/', $item)
                // phpcs:ignore Magento2.Functions.DiscouragedFunction
                && strpos(basename($item), '.') !== 0) {
                if ($index === false || strlen($leftover) === $index + 1) {
                    yield $this->normalizeAbsolutePath(
                        $this->isDirectory($item) ? rtrim($item, '/') . '/' : $item
                    );
                } elseif (strlen($leftover) > $index + 1) {
                    yield from $this->glob("{$parentDirectory}/{$item}" . substr($leftover, $index));
                }
            }
        }

        return $directoryContent;
    }
}
