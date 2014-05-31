<?php
/**
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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\Filesystem\Directory;

interface WriteInterface extends ReadInterface
{
    /**
     * Create directory if it does not exists
     *
     * @param string $path [optional]
     * @return bool
     * @throws \Magento\Framework\Filesystem\FilesystemException
     */
    public function create($path = null);

    /**
     * Delete given path
     *
     * @param string $path [optional]
     * @return bool
     * @throws \Magento\Framework\Filesystem\FilesystemException
     */
    public function delete($path = null);

    /**
     * Rename a file
     *
     * @param string $path
     * @param string $newPath
     * @param WriteInterface $targetDirectory [optional]
     * @return bool
     * @throws \Magento\Framework\Filesystem\FilesystemException
     */
    public function renameFile($path, $newPath, WriteInterface $targetDirectory = null);

    /**
     * Copy a file
     *
     * @param string $path
     * @param string $destination
     * @param WriteInterface $targetDirectory [optional]
     * @return bool
     * @throws \Magento\Framework\Filesystem\FilesystemException
     */
    public function copyFile($path, $destination, WriteInterface $targetDirectory = null);

    /**
     * Change permissions of given path
     *
     * @param string $path
     * @param int $permissions
     * @return bool
     * @throws \Magento\Framework\Filesystem\FilesystemException
     */
    public function changePermissions($path, $permissions);

    /**
     * Sets access and modification time of file.
     *
     * @param string $path
     * @param int $modificationTime [optional]
     * @return bool
     * @throws \Magento\Framework\Filesystem\FilesystemException
     */
    public function touch($path, $modificationTime = null);

    /**
     * Check if given path is writable
     *
     * @param string $path [optional]
     * @return bool
     */
    public function isWritable($path = null);

    /**
     * Open file in given mode
     *
     * @param string $path
     * @param string $mode
     * @param string|null $protocol
     * @return \Magento\Framework\Filesystem\File\WriteInterface
     */
    public function openFile($path, $mode = 'w', $protocol = null);

    /**
     * Open file in given path
     *
     * @param string $path
     * @param string $content
     * @param string $mode [optional]
     * @return int The number of bytes that were written.
     * @throws \Magento\Framework\Filesystem\FilesystemException
     */
    public function writeFile($path, $content, $mode = null);

    /**
     * Get driver
     *
     * @return \Magento\Framework\Filesystem\DriverInterface
     */
    public function getDriver();
}
