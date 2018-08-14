<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Mtf\Util\Filesystem;

/**
 * Filesystem helper.
 */
class FileHelper
{
    /**
     * Normalizes a file/directory path.
     *
     * @param string $path
     * @param string $ds
     * @return string
     */
    public function normalizePath($path, $ds = DIRECTORY_SEPARATOR)
    {
        $path = rtrim(strtr($path, '/\\', $ds . $ds), $ds);
        if (strpos($ds . $path, "{$ds}.") === false && strpos($path, "{$ds}{$ds}") === false) {
            return $path;
        }

        return $this->realpath($ds, $path);
    }

    /**
     * Returns canonicalized pathname.
     *
     * @param string $ds
     * @param string $path
     * @return string
     */
    private function realpath($ds, $path)
    {
        $parts = [];
        foreach (explode($ds, $path) as $part) {
            if ($part === '..' && !empty($parts) && end($parts) !== '..') {
                array_pop($parts);
            } elseif ($part === '.' || $part === '' && !empty($parts)) {
                continue;
            } else {
                $parts[] = $part;
            }
        }

        $path = implode($ds, $parts);

        return $path === '' ? '.' : $path;
    }

    /**
     * Creates a new directory.
     *
     * @param string $path
     * @param int $mode
     * @param bool $recursive
     * @return bool
     * @throws \Exception
     */
    public function createDirectory($path, $mode = 0775, $recursive = true)
    {
        if (is_dir($path)) {
            return true;
        }
        $parentDir = dirname($path);

        if ($recursive && !is_dir($parentDir) && $parentDir !== $path) {
            $this->createDirectory($parentDir, $mode, true);
        }

        try {
            if (!mkdir($path, $mode)) {
                return false;
            }
        } catch (\Exception $e) {
            if (!is_dir($path)) {
                throw new \Exception("Failed to create directory \"$path\"");
            }
        }

        try {
            return chmod($path, $mode);
        } catch (\Exception $e) {
            throw new \Exception("Failed to change permissions for directory \"$path\"");
        }
    }

    /**
     * Create a new file with content.
     *
     * @param string $filename
     * @param string $content
     * @return bool
     */
    public function createFile($filename, $content)
    {
        return file_put_contents($filename, $content) !== false;
    }
}
