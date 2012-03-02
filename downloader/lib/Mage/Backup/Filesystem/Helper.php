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
 * @category    Mage
 * @package     Mage_Backup
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Filesystem helper for Mage_Backup library
 *
 * @category    Mage
 * @package     Mage_Backup
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Backup_Filesystem_Helper
{
    /**
     * Constant can be used in getInfo() function as second parameter.
     * Check whether directory and all files/sub directories are writable
     *
     * @const int
     */
    const INFO_WRITABLE = 1;

    /**
     * Constant can be used in getInfo() function as second parameter.
     * Check whether directory and all files/sub directories are readable
     *
     * @const int
     */
    const INFO_READABLE  = 2;

    /**
     * Constant can be used in getInfo() function as second parameter.
     * Get directory size
     *
     * @const int
     */
    const INFO_SIZE      = 4;

    /**
     * Constant can be used in getInfo() function as second parameter.
     * Combination of INFO_WRITABLE, INFO_READABLE, INFO_SIZE
     *
     * @const int
     */
    const INFO_ALL       = 7;

    /**
     * Recursively delete $path
     *
     * @param string $path
     * @param array $skipPaths
     * @param bool $removeRoot
     * @throws Mage_Exception
     */
    public function rm($path, $skipPaths = array(), $removeRoot = false)
    {
        $filesystemIterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path), RecursiveIteratorIterator::CHILD_FIRST
        );

        $iterator = new Mage_Backup_Filesystem_Iterator_Filter($filesystemIterator, $skipPaths);

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
     */
    public function getInfo($path, $infoOptions = self::INFO_ALL, $skipFiles = array())
    {
        $info = array();
        if ($infoOptions & self::INFO_READABLE) {
            $info['readable'] = true;
        }

        if ($infoOptions & self::INFO_WRITABLE) {
            $info['writable'] = true;
        }

        if ($infoOptions & self::INFO_SIZE) {
            $info['size'] = 0;
        }

        $filesystemIterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path), RecursiveIteratorIterator::CHILD_FIRST
        );

        $iterator = new Mage_Backup_Filesystem_Iterator_Filter($filesystemIterator, $skipFiles);

        foreach ($iterator as $item) {
            if (($infoOptions & self::INFO_WRITABLE) && !$item->isWritable()) {
                $info['writable'] = false;
            }

            if (($infoOptions & self::INFO_READABLE) && !$item->isReadable()) {
                $info['readable'] = false;
            }

            if ($infoOptions & self::INFO_SIZE && !$item->isDir()) {
                $info['size'] += $item->getSize();
            }
        }

        return $info;
    }
}
