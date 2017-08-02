<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Filesystem\Io;

use Magento\Framework\Filesystem\DriverInterface;

/**
 * Input/output client interface
 * @since 2.0.0
 */
interface IoInterface
{
    /**
     * Open a connection
     *
     * @param array $args
     * @return bool
     * @since 2.0.0
     */
    public function open(array $args = []);

    /**
     * Close a connection
     *
     * @return bool
     * @since 2.0.0
     */
    public function close();

    /**
     * Create a directory
     *
     * @param string $dir
     * @param int $mode
     * @param bool $recursive
     * @return bool
     * @since 2.0.0
     */
    public function mkdir($dir, $mode = 0777, $recursive = true);

    /**
     * Delete a directory
     *
     * @param string $dir
     * @param bool $recursive
     * @return bool
     * @since 2.0.0
     */
    public function rmdir($dir, $recursive = false);

    /**
     * Get current working directory
     *
     * @return string
     * @since 2.0.0
     */
    public function pwd();

    /**
     * Change current working directory
     *
     * @param string $dir
     * @return bool
     * @SuppressWarnings(PHPMD.ShortMethodName)
     * @since 2.0.0
     */
    public function cd($dir);

    /**
     * Read a file
     *
     * @param string $filename
     * @param string|resource|null $dest
     * @return string|bool
     * @since 2.0.0
     */
    public function read($filename, $dest = null);

    /**
     * Write a file
     *
     * @param string $filename
     * @param string|resource $src
     * @param int|null $mode
     * @return int|bool
     * @since 2.0.0
     */
    public function write($filename, $src, $mode = null);

    /**
     * Delete a file
     *
     * @param string $filename
     * @return bool
     * @SuppressWarnings(PHPMD.ShortMethodName)
     * @since 2.0.0
     */
    public function rm($filename);

    /**
     * Rename or move a directory or a file
     *
     * @param string $src
     * @param string $dest
     * @return bool
     * @SuppressWarnings(PHPMD.ShortMethodName)
     * @since 2.0.0
     */
    public function mv($src, $dest);

    /**
     * Change mode of a directory or a file
     *
     * @param string $filename
     * @param int $mode
     * @return bool
     * @since 2.0.0
     */
    public function chmod($filename, $mode);

    /**
     * Get list of cwd subdirectories and files
     *
     * @param string|null $grep
     * @return array
     * @SuppressWarnings(PHPMD.ShortMethodName)
     * @since 2.0.0
     */
    public function ls($grep = null);

    /**
     * Retrieve directory separator in context of io resource
     *
     * @return string
     * @since 2.0.0
     */
    public function dirsep();
}
