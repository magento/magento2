<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Filesystem\Io;

use Magento\Framework\Filesystem\DriverInterface;

/**
 * Input/output client interface
 */
interface IoInterface
{
    /**
     * Open a connection
     *
     * @param array $args
     * @return bool
     */
    public function open(array $args = []);

    /**
     * Close a connection
     *
     * @return bool
     */
    public function close();

    /**
     * Create a directory
     *
     * @param string $dir
     * @param int $mode
     * @param bool $recursive
     * @return bool
     */
    public function mkdir($dir, $mode = 0777, $recursive = true);

    /**
     * Delete a directory
     *
     * @param string $dir
     * @param bool $recursive
     * @return bool
     */
    public function rmdir($dir, $recursive = false);

    /**
     * Get current working directory
     *
     * @return string
     */
    public function pwd();

    /**
     * Change current working directory
     *
     * @param string $dir
     * @return bool
     * @SuppressWarnings(PHPMD.ShortMethodName)
     */
    public function cd($dir);

    /**
     * Read a file
     *
     * @param string $filename
     * @param string|resource|null $dest
     * @return string|bool
     */
    public function read($filename, $dest = null);

    /**
     * Write a file
     *
     * @param string $filename
     * @param string|resource $src
     * @param int|null $mode
     * @return int|bool
     */
    public function write($filename, $src, $mode = null);

    /**
     * Delete a file
     *
     * @param string $filename
     * @return bool
     * @SuppressWarnings(PHPMD.ShortMethodName)
     */
    public function rm($filename);

    /**
     * Rename or move a directory or a file
     *
     * @param string $src
     * @param string $dest
     * @return bool
     * @SuppressWarnings(PHPMD.ShortMethodName)
     */
    public function mv($src, $dest);

    /**
     * Change mode of a directory or a file
     *
     * @param string $filename
     * @param int $mode
     * @return bool
     */
    public function chmod($filename, $mode);

    /**
     * Get list of cwd subdirectories and files
     *
     * @param string|null $grep
     * @return array
     * @SuppressWarnings(PHPMD.ShortMethodName)
     */
    public function ls($grep = null);

    /**
     * Retrieve directory separator in context of io resource
     *
     * @return string
     */
    public function dirsep();
}
