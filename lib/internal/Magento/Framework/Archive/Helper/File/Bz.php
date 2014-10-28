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

/**
 * Helper class that simplifies bz2 files stream reading and writing
 */
namespace Magento\Framework\Archive\Helper\File;

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
            throw new \Magento\Framework\Exception('Failed to open file ' . $this->_filePath);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function _write($data)
    {
        $result = bzwrite($this->_fileHandler, $data);

        if (false === $result) {
            throw new \Magento\Framework\Exception('Failed to write data to ' . $this->_filePath);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function _read($length)
    {
        $data = bzread($this->_fileHandler, $length);

        if (false === $data) {
            throw new \Magento\Framework\Exception('Failed to read data from ' . $this->_filePath);
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
