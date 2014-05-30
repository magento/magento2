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
namespace Magento\Framework\Filesystem\File;

interface ReadInterface
{
    /**
     * Reads the specified number of bytes from the current position.
     *
     * @param int $length The number of bytes to read
     * @return string
     */
    public function read($length);

    /**
     * Reads the line with specified number of bytes from the current position.
     *
     * @param int $length The number of bytes to read
     * @param string $ending [optional]
     * @return string
     */
    public function readLine($length, $ending = null);

    /**
     * Reads one CSV row from the file
     *
     * @param int $length [optional] <p>
     * @param string $delimiter [optional]
     * @param string $enclosure [optional]
     * @param string $escape [optional]
     * @return array|bool false on end of file
     */
    public function readCsv($length = 0, $delimiter = ',', $enclosure = '"', $escape = '\\');

    /**
     * Returns the current position
     *
     * @return int
     */
    public function tell();

    /**
     * Seeks to the specified offset
     *
     * @param int $length
     * @param int $whence
     * @return int
     */
    public function seek($length, $whence = SEEK_SET);

    /**
     * Checks if the current position is the end-of-file
     *
     * @return bool
     */
    public function eof();

    /**
     * Closes the file.
     *
     * @return bool
     */
    public function close();

    /**
     * Get file properties.
     *
     * @return array
     */
    public function stat();
}
