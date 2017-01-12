<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\System;

use Magento\Framework\Filesystem\DriverInterface;

class Dirs
{
    /**
     * @param string[]|string $dirname
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ShortMethodName)
     */
    public static function rm($dirname)
    {
        if (is_array($dirname)) {
            $dirname = $dirname[1];
        }
        // Sanity check
        if (!@file_exists($dirname)) {
            return false;
        }

        // Simple delete for a file
        if (@is_file($dirname) || @is_link($dirname)) {
            return unlink($dirname);
        }

        // Create and iterate stack
        $stack = [$dirname];
        while ($entry = array_pop($stack)) {
            // Watch for symlinks
            if (@is_link($entry)) {
                @unlink($entry);
                continue;
            }

            // Attempt to remove the directory
            if (@rmdir($entry)) {
                continue;
            }

            // Otherwise add it to the stack
            $stack[] = $entry;
            $dh = opendir($entry);
            while (false !== ($child = readdir($dh))) {
                // Ignore pointers
                if ($child === '.' || $child === '..') {
                    continue;
                }
                // Unlink files and add directories to stack
                $child = $entry . '/' . $child;
                if (is_dir($child) && !is_link($child)) {
                    $stack[] = $child;
                } else {
                    @unlink($child);
                }
            }
            @closedir($dh);
        }
        return true;
    }

    /**
     * Attempts to create the directory
     *
     * @param string $path
     * @param bool $recursive
     * @param int $mode
     * @return true
     * @throws \Exception
     */
    public static function mkdirStrict($path, $recursive = true, $mode = 0777)
    {
        $exists = file_exists($path);
        if ($exists && is_dir($path)) {
            return true;
        }
        if ($exists && !is_dir($path)) {
            throw new \Exception("'{$path}' already exists, should be a dir, not a file!");
        }
        $out = @mkdir($path, $mode, $recursive);
        if (false === $out) {
            throw new \Exception("Can't create dir: '{$path}'");
        }
        return true;
    }

    /**
     * @param string $source
     * @param string $dest
     * @return void
     * @throws \Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public static function copyFileStrict($source, $dest)
    {
        $exists = file_exists($source);
        if (!$exists) {
            throw new \Exception('No file exists: ' . $exists);
        }
    }
}
