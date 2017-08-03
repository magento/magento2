<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Helper class that simplifies bz2 files stream reading and writing
 */
namespace Magento\Framework\Archive\Helper\File;

/**
 * Class \Magento\Framework\Archive\Helper\File\Bz
 *
 */
class Bz extends \Magento\Framework\Archive\Helper\File
{
    /**
     * {@inheritdoc}
     * @throws \RuntimeException
     */
    protected function _open($mode)
    {
        if (!extension_loaded('bz2')) {
            throw new \RuntimeException('PHP extension bz2 is required.');
        }
        $this->_fileHandler = bzopen($this->_filePath, $mode);

        if (false === $this->_fileHandler) {
            throw new \Magento\Framework\Exception\LocalizedException(
                new \Magento\Framework\Phrase('Failed to open file %1', [$this->_filePath])
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function _write($data)
    {
        $result = bzwrite($this->_fileHandler, $data);

        if (false === $result) {
            throw new \Magento\Framework\Exception\LocalizedException(
                new \Magento\Framework\Phrase('Failed to write data to %1', [$this->_filePath])
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function _read($length)
    {
        $data = bzread($this->_fileHandler, $length);

        if (false === $data) {
            throw new \Magento\Framework\Exception\LocalizedException(
                new \Magento\Framework\Phrase('Failed to read data from %1', [$this->_filePath])
            );
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    protected function _close()
    {
        bzclose($this->_fileHandler);
    }
}
