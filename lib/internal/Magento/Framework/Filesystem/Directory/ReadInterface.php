<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Filesystem\Directory;

interface ReadInterface
{
    /**
     * Get absolute path
     *
     * @param string $path [optional]
     * @return string
     */
    public function getAbsolutePath($path = null);

    /**
     * Get relative path
     *
     * @param string $path
     * @return string
     */
    public function getRelativePath($path = null);

    /**
     * Retrieve list of all entities in given path
     *
     * @param string $path [optional]
     * @return array
     */
    public function read($path = null);

    /**
     * Search all entries for given regex pattern
     *
     * @param string $pattern
     * @param string $path [optional]
     * @return array
     */
    public function search($pattern, $path = null);

    /**
     * Check a file or directory exists
     *
     * @param string $path [optional]
     * @return bool
     */
    public function isExist($path = null);

    /**
     * Gathers the statistics of the given path
     *
     * @param string $path
     * @return array
     */
    public function stat($path);

    /**
     * Check permissions for reading file or directory
     *
     * @param string $path [optional]
     * @return bool
     */
    public function isReadable($path = null);

    /**
     * Check whether given path is file
     *
     * @param string $path
     * @return bool
     */
    public function isFile($path);

    /**
     * Check whether given path is directory
     *
     * @param string $path [optional]
     * @return bool
     */
    public function isDirectory($path = null);

    /**
     * Open file in read mode
     *
     * @param string $path
     * @return \Magento\Framework\Filesystem\File\ReadInterface
     */
    public function openFile($path);

    /**
     * Retrieve file contents from given path
     *
     * @param string $path
     * @param string|null $flag
     * @param resource|null $context
     * @return string
     * @throws \Magento\Framework\Filesystem\FilesystemException
     */
    public function readFile($path, $flag = null, $context = null);
}
