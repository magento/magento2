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

use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Filesystem\FilesystemException;

class Write extends Read implements WriteInterface
{
    /**
     * Constructor
     *
     * @param string $path
     * @param DriverInterface $driver
     * @param string $mode
     */
    public function __construct($path, DriverInterface $driver, $mode)
    {
        $this->mode = $mode;
        parent::__construct($path, $driver);
    }

    /**
     * Assert file existence for proper mode
     *
     * @return void
     * @throws \Magento\Framework\Filesystem\FilesystemException
     */
    protected function assertValid()
    {
        $fileExists = $this->driver->isExists($this->path);
        if (!$fileExists && preg_match('/r/', $this->mode)) {
            throw new FilesystemException(sprintf('The file "%s" doesn\'t exist', $this->path));
        } elseif ($fileExists && preg_match('/x/', $this->mode)) {
            throw new FilesystemException(sprintf('The file "%s" already exists', $this->path));
        }
    }

    /**
     * Writes the data to file.
     *
     * @param string $data
     * @return int
     * @throws FilesystemException
     */
    public function write($data)
    {
        try {
            return $this->driver->fileWrite($this->resource, $data);
        } catch (FilesystemException $e) {
            throw new FilesystemException(sprintf('Cannot write to the "%s" file. %s', $this->path, $e->getMessage()));
        }
    }

    /**
     * Writes one CSV row to the file.
     *
     * @param array $data
     * @param string $delimiter
     * @param string $enclosure
     * @return int
     * @throws FilesystemException
     */
    public function writeCsv(array $data, $delimiter = ',', $enclosure = '"')
    {
        try {
            return $this->driver->filePutCsv($this->resource, $data, $delimiter, $enclosure);
        } catch (FilesystemException $e) {
            throw new FilesystemException(sprintf('Cannot write to the "%s" file. %s', $this->path, $e->getMessage()));
        }
    }

    /**
     * Flushes the output.
     *
     * @return bool
     * @throws FilesystemException
     */
    public function flush()
    {
        try {
            return $this->driver->fileFlush($this->resource);
        } catch (FilesystemException $e) {
            throw new FilesystemException(sprintf('Cannot flush the "%s" file. %s', $this->path, $e->getMessage()));
        }
    }

    /**
     * Portable advisory file locking
     *
     * @param int $lockMode
     * @return bool
     */
    public function lock($lockMode = LOCK_EX)
    {
        return $this->driver->fileLock($this->resource, $lockMode);
    }

    /**
     * File unlocking
     *
     * @return bool
     */
    public function unlock()
    {
        return $this->driver->fileUnlock($this->resource);
    }
}
