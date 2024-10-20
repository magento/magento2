<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Backup\Filesystem;

use Magento\Framework\Backup\Filesystem\Iterator\Filter;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Filesystem helper
 */
class Helper
{
    /**
     * Constant can be used in getInfo() function as second parameter.
     * Check whether directory and all files/sub directories are writable
     *
     * @const int
     */
    public const INFO_WRITABLE = 1;

    /**
     * Constant can be used in getInfo() function as second parameter.
     * Check whether directory and all files/sub directories are readable
     *
     * @const int
     */
    public const INFO_READABLE = 2;

    /**
     * Constant can be used in getInfo() function as second parameter.
     * Get directory size
     *
     * @const int
     */
    public const INFO_SIZE = 4;

    /**
     * Constant can be used in getInfo() function as second parameter.
     * Combination of INFO_WRITABLE, INFO_READABLE, INFO_SIZE
     *
     * @const int
     */
    public const INFO_ALL = 7;

    /**
     * Recursively delete $path
     *
     * @param string $path
     * @param array $skipPaths
     * @param bool $removeRoot
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.ShortMethodName)
     */
    public function rm($path, $skipPaths = [], $removeRoot = false)
    {
        $filesystemIterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        $iterator = new Filter($filesystemIterator, $skipPaths);

        foreach ($iterator as $item) {
            $item->isDir() ? @rmdir($item->__toString()) : @unlink($item->__toString());
        }

        if ($removeRoot && is_dir($path)) {
            @rmdir($path);
        }
    }

    /**
     * Get information (readable, writable, size) about $path
     *
     * @param string $path
     * @param int $infoOptions
     * @param array $skipFiles
     *
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function getInfo($path, $infoOptions = self::INFO_ALL, $skipFiles = [])
    {
        $info = [];
        if ($infoOptions & self::INFO_READABLE) {
            $info['readable'] = true;
            $info['readableMeta'] = [];
        }

        if ($infoOptions & self::INFO_WRITABLE) {
            $info['writable'] = true;
            $info['writableMeta'] = [];
        }

        if ($infoOptions & self::INFO_SIZE) {
            $info['size'] = 0;
        }

        $filesystemIterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        $iterator = new Filter($filesystemIterator, $skipFiles);

        foreach ($iterator as $item) {
            if ($item->isLink()) {
                continue;
            }

            if ($infoOptions & self::INFO_WRITABLE && !$item->isWritable()) {
                $info['writable'] = false;
                $info['writableMeta'][] = $item->getPathname();
            }

            if ($infoOptions & self::INFO_READABLE && !$item->isReadable()) {
                $info['readable'] = false;
                $info['readableMeta'][] = $item->getPathname();
            }

            if ($infoOptions & self::INFO_SIZE && !$item->isDir()) {
                $info['size'] += $item->getSize();
            }
        }

        return $info;
    }
}
