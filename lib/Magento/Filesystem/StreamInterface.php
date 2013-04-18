<?php
/**
 * Interface of Magento filesystem stream
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
interface Magento_Filesystem_StreamInterface
{
    /**
     * Opens the stream in the specified mode
     *
     * @param Magento_Filesystem_Stream_Mode|string $mode
     * @throws Magento_Filesystem_Exception
     */
    public function open($mode);

    /**
     * Reads the specified number of bytes from the current position.
     *
     * @param integer $count The number of bytes to read
     * @return string
     * @throws Magento_Filesystem_Exception
     */
    public function read($count);

    /**
     * Reads one CSV row from the stream
     *
     * @param int $count [optional] <p>
     * Must be greater than the longest line (in characters) to be found in
     * the CSV file (allowing for trailing line-end characters). It became
     * optional in PHP 5. Omitting this parameter (or setting it to 0 in PHP
     * 5.0.4 and later) the maximum line length is not limited, which is
     * slightly slower.
     * @param string $delimiter
     * @param string $enclosure
     * @return array|bool false on end of file
     * @throws Magento_Filesystem_Exception
     */
    public function readCsv($count = 0, $delimiter = ',', $enclosure = '"');

    /**
     * Writes the data to stream.
     *
     * @param string $data
     * @return integer
     * @throws Magento_Filesystem_Exception
     */
    public function write($data);

    /**
     * Writes one CSV row to the stream
     *
     * @param array $data
     * @param string $delimiter
     * @param string $enclosure
     * @return integer
     * @throws Magento_Filesystem_Exception
     */
    public function writeCsv(array $data, $delimiter = ',', $enclosure = '"');

    /**
     * Closes the stream.
     *
     * @throws Magento_Filesystem_Exception
     */
    public function close();

    /**
     * Flushes the output.
     *
     * @throws Magento_Filesystem_Exception
     */
    public function flush();

    /**
     * Seeks to the specified offset
     *
     * @param int $offset
     * @param int $whence
     * @throws Magento_Filesystem_Exception
     */
    public function seek($offset, $whence = SEEK_SET);

    /**
     * Returns the current position
     *
     * @return int
     * @throws Magento_Filesystem_Exception
     */
    public function tell();

    /**
     * Checks if the current position is the end-of-file
     *
     * @return bool
     * @throws Magento_Filesystem_Exception
     */
    public function eof();

    /**
     * Portable advisory file locking
     *
     * @param bool $exclusive
     * @throws Magento_Filesystem_Exception
     */
    public function lock($exclusive = true);

    /**
     * File unlocking
     *
     * @throws Magento_Filesystem_Exception
     */
    public function unlock();
}
