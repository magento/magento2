<?php
/**
 * Magento filesystem facade
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework;

use Magento\Framework\Filesystem\DriverPool;

/**
 * @api
 */
class Filesystem
{
    /**
     * @var \Magento\Framework\Filesystem\DirectoryList
     */
    protected $directoryList;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadFactory
     */
    protected $readFactory;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteFactory
     */
    protected $writeFactory;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadInterface[]
     */
    protected $readInstances = [];

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface[]
     */
    protected $writeInstances = [];

    /**
     * @param Filesystem\DirectoryList $directoryList
     * @param Filesystem\Directory\ReadFactory $readFactory
     * @param Filesystem\Directory\WriteFactory $writeFactory
     */
    public function __construct(
        \Magento\Framework\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\Filesystem\Directory\ReadFactory $readFactory,
        \Magento\Framework\Filesystem\Directory\WriteFactory $writeFactory
    ) {
        $this->directoryList = $directoryList;
        $this->readFactory = $readFactory;
        $this->writeFactory = $writeFactory;
    }

    /**
     * Create an instance of directory with read permissions
     *
     * @param string $directoryCode
     * @param string $driverCode
     * @return \Magento\Framework\Filesystem\Directory\ReadInterface
     */
    public function getDirectoryRead($directoryCode, $driverCode = DriverPool::FILE)
    {
        $code = $directoryCode . '_' . $driverCode;
        if (!array_key_exists($code, $this->readInstances)) {
            $this->readInstances[$code] = $this->readFactory->create($this->getDirPath($directoryCode), $driverCode);
        }
        return $this->readInstances[$code];
    }

    /**
     * Create an instance of directory with read permissions by path.
     *
     * @param string $path
     * @param string $driverCode
     *
     * @return \Magento\Framework\Filesystem\Directory\ReadInterface
     *
     */
    public function getDirectoryReadByPath($path, $driverCode = DriverPool::FILE)
    {
        return $this->readFactory->create($path, $driverCode);
    }

    /**
     * Create an instance of directory with write permissions
     *
     * @param string $directoryCode
     * @param string $driverCode
     * @return \Magento\Framework\Filesystem\Directory\WriteInterface
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function getDirectoryWrite($directoryCode, $driverCode = DriverPool::FILE)
    {
        $code = $directoryCode . '_' . $driverCode;
        if (!array_key_exists($code, $this->writeInstances)) {
            $this->writeInstances[$code] = $this->writeFactory->create($this->getDirPath($directoryCode), $driverCode);
        }
        return $this->writeInstances[$code];
    }

    /**
     * Gets configuration of a directory
     *
     * @param string $code
     * @return string
     */
    protected function getDirPath($code)
    {
        return $this->directoryList->getPath($code);
    }

    /**
     * Retrieve uri for given code
     *
     * @param string $code
     * @return string
     */
    public function getUri($code)
    {
        return $this->directoryList->getUrlPath($code);
    }
}
