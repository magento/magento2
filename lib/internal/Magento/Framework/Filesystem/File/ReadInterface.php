<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Filesystem\File;

/**
 * Interface \Magento\Framework\Filesystem\File\ReadInterface
 *
 * @since 2.0.0
 */
interface ReadInterface
{
    /**
     * Reads the specified number of bytes from the current position.
     *
     * @param int $length The number of bytes to read
     * @return string
     * @since 2.0.0
     */
    public function read($length);

    /**
     * Reads the line with specified number of bytes from the current position.
     *
     * @param int $length The number of bytes to read
     * @param string $ending [optional]
     * @return string
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function readCsv($length = 0, $delimiter = ',', $enclosure = '"', $escape = '\\');

    /**
     * Returns the current position
     *
     * @return int
     * @since 2.0.0
     */
    public function tell();

    /**
     * Seeks to the specified offset
     *
     * @param int $length
     * @param int $whence
     * @return int
     * @since 2.0.0
     */
    public function seek($length, $whence = SEEK_SET);

    /**
     * Checks if the current position is the end-of-file
     *
     * @return bool
     * @since 2.0.0
     */
    public function eof();

    /**
     * Closes the file.
     *
     * @return bool
     * @since 2.0.0
     */
    public function close();

    /**
     * Get file properties.
     *
     * @return array
     * @since 2.0.0
     */
    public function stat();
}
