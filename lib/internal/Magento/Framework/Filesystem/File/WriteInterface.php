<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Filesystem\File;

/**
 * @api
 * @since 2.0.0
 */
interface WriteInterface extends ReadInterface
{
    /**
     * Writes the data to file.
     *
     * @param string $data
     * @return int
     * @throws \Magento\Framework\Exception\FileSystemException
     * @since 2.0.0
     */
    public function write($data);

    /**
     * Writes one CSV row to the file.
     *
     * @param array $data
     * @param string $delimiter
     * @param string $enclosure
     * @return int
     * @throws \Magento\Framework\Exception\FileSystemException
     * @since 2.0.0
     */
    public function writeCsv(array $data, $delimiter = ',', $enclosure = '"');

    /**
     * Flushes the output.
     *
     * @return bool
     * @throws \Magento\Framework\Exception\FileSystemException
     * @since 2.0.0
     */
    public function flush();

    /**
     * Portable advisory file locking
     *
     * @param int $lockMode
     * @return bool
     * @since 2.0.0
     */
    public function lock($lockMode = LOCK_EX);

    /**
     * File unlocking
     *
     * @return bool
     * @since 2.0.0
     */
    public function unlock();
}
