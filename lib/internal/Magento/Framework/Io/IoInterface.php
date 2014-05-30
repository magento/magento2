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
 * @copyright  Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\Io;

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
    public function open(array $args = array());

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
     */
    public function rm($filename);

    /**
     * Rename or move a directory or a file
     *
     * @param string $src
     * @param string $dest
     * @return bool
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
     */
    public function ls($grep = null);

    /**
     * Retrieve directory separator in context of io resource
     *
     * @return string
     */
    public function dirsep();
}
