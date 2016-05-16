<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Filesystem\File;

use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Exception\FileSystemException;

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
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    protected function assertValid()
    {
        $fileExists = $this->driver->isExists($this->path);
        if (!$fileExists && preg_match('/r/', $this->mode)) {
            throw new FileSystemException(new \Magento\Framework\Phrase('The file "%1" doesn\'t exist', [$this->path]));
        } elseif ($fileExists && preg_match('/x/', $this->mode)) {
            throw new FileSystemException(new \Magento\Framework\Phrase('The file "%1" already exists', [$this->path]));
        }
    }

    /**
     * Writes the data to file.
     *
     * @param string $data
     * @return int
     * @throws FileSystemException
     */
    public function write($data)
    {
        try {
            return $this->driver->fileWrite($this->resource, $data);
        } catch (FileSystemException $e) {
            throw new FileSystemException(
                new \Magento\Framework\Phrase('Cannot write to the "%1" file. %2', [$this->path, $e->getMessage()])
            );
        }
    }

    /**
     * Writes one CSV row to the file.
     *
     * @param array $data
     * @param string $delimiter
     * @param string $enclosure
     * @return int
     * @throws FileSystemException
     */
    public function writeCsv(array $data, $delimiter = ',', $enclosure = '"')
    {
        try {
            return $this->driver->filePutCsv($this->resource, $data, $delimiter, $enclosure);
        } catch (FileSystemException $e) {
            throw new FileSystemException(
                new \Magento\Framework\Phrase('Cannot write to the "%1" file. %2', [$this->path, $e->getMessage()])
            );
        }
    }

    /**
     * Flushes the output.
     *
     * @return bool
     * @throws FileSystemException
     */
    public function flush()
    {
        try {
            return $this->driver->fileFlush($this->resource);
        } catch (FileSystemException $e) {
            throw new FileSystemException(
                new \Magento\Framework\Phrase('Cannot flush the "%1" file. %2', [$this->path, $e->getMessage()])
            );
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
