<?php
/**
 * Interface of Magento filesystem adapter
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Filesystem;

interface AdapterInterface
{
    /**
     * Checks the file existence.
     *
     * @param string $key
     * @return bool
     */
    public function exists($key);

    /**
     * Reads content of the file.
     *
     * @param string $key
     * @return string
     */
    public function read($key);

    /**
     * Writes content into the file.
     *
     * @param string $key
     * @param string $content
     * @return bool true if write was success
     */
    public function write($key, $content);

    /**
     * Renames the file.
     *
     * @param string $source
     * @param string $target
     * @return bool
     */
    public function rename($source, $target);

    /**
     * Copy the file.
     *
     * @param string $source
     * @param string $target
     * @return bool
     */
    public function copy($source, $target);

    /**
     * Deletes the file or directory recursively.
     *
     * @param string $key
     * @throws \Magento\Filesystem\FilesystemException
     */
    public function delete($key);

    /**
     * Changes permissions of filesystem key
     *
     * @param string $key
     * @param int $permissions
     * @param bool $recursively
     * @throws \Magento\Filesystem\FilesystemException
     */
    public function changePermissions($key, $permissions, $recursively);

    /**
     * Gets list of all nested keys
     *
     * @param string $key
     * @return array
     * @throws \Magento\Filesystem\FilesystemException
     */
    public function getNestedKeys($key);

    /**
     * Gets list of all matched keys
     *
     * @param string $pattern
     * @return array
     * @throws \Magento\Filesystem\FilesystemException
     */
    public function searchKeys($pattern);

    /**
     * Check if key is directory.
     *
     * @param string $key
     * @return bool
     */
    public function isDirectory($key);

    /**
     * Check if key is file.
     *
     * @param string $key
     * @return bool
     */
    public function isFile($key);

    /**
     * Check if file exists and is writable
     *
     * @param string $key
     * @return bool
     */
    public function isWritable($key);

    /**
     * Check if file exists and is readable
     *
     * @param string $key
     * @return bool
     */
    public function isReadable($key);

    /**
     * Calculates the MD5 hash of the file specified
     *
     * @param $key
     * @return string
     * @throws \Magento\Filesystem\FilesystemException
     */
    public function getFileMd5($key);

    /**
     * Creates new directory
     *
     * @param string $key
     * @param int $mode
     * @throws \Magento\Filesystem\FilesystemException If cannot create directory
     */
    public function createDirectory($key, $mode);

    /**
     * Touches a file
     *
     * @param string $key
     * @param int|null $fileModificationTime
     * @throws \Magento\Filesystem\FilesystemException
     */
    public function touch($key, $fileModificationTime = null);

    /**
     * Get file modification time.
     *
     * @param string $key
     * @return int
     */
    public function getMTime($key);

    /**
     * Get file size.
     *
     * @param string $key
     * @return int
     */
    public function getFileSize($key);
}
