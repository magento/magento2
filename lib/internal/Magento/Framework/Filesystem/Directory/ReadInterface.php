<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Filesystem\Directory;

/**
 * Interface \Magento\Framework\Filesystem\Directory\ReadInterface
 *
 * @since 2.0.0
 */
interface ReadInterface
{
    /**
     * Get absolute path
     *
     * @param string $path [optional]
     * @return string
     * @since 2.0.0
     */
    public function getAbsolutePath($path = null);

    /**
     * Get relative path
     *
     * @param string $path
     * @return string
     * @since 2.0.0
     */
    public function getRelativePath($path = null);

    /**
     * Retrieve list of all entities in given path
     *
     * @param string $path [optional]
     * @return array
     * @since 2.0.0
     */
    public function read($path = null);

    /**
     * Search all entries for given regex pattern
     *
     * @param string $pattern
     * @param string $path [optional]
     * @return array
     * @since 2.0.0
     */
    public function search($pattern, $path = null);

    /**
     * Check a file or directory exists
     *
     * @param string $path [optional]
     * @return bool
     * @since 2.0.0
     */
    public function isExist($path = null);

    /**
     * Gathers the statistics of the given path
     *
     * @param string $path
     * @return array
     * @since 2.0.0
     */
    public function stat($path);

    /**
     * Check permissions for reading file or directory
     *
     * @param string $path [optional]
     * @return bool
     * @since 2.0.0
     */
    public function isReadable($path = null);

    /**
     * Check whether given path is file
     *
     * @param string $path
     * @return bool
     * @since 2.0.0
     */
    public function isFile($path);

    /**
     * Check whether given path is directory
     *
     * @param string $path [optional]
     * @return bool
     * @since 2.0.0
     */
    public function isDirectory($path = null);

    /**
     * Open file in read mode
     *
     * @param string $path
     * @return \Magento\Framework\Filesystem\File\ReadInterface
     * @since 2.0.0
     */
    public function openFile($path);

    /**
     * Retrieve file contents from given path
     *
     * @param string $path
     * @param string|null $flag
     * @param resource|null $context
     * @return string
     * @throws \Magento\Framework\Exception\FileSystemException
     * @since 2.0.0
     */
    public function readFile($path, $flag = null, $context = null);
}
