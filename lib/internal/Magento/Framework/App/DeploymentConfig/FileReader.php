<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\DeploymentConfig;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\DriverPool;

/**
 * Allows to read configurations from different config files.
 *
 * @see Reader The reader for merged configurations
 */
class FileReader
{
    /**
     * The list of directories.
     *
     * @var DirectoryList
     */
    private $dirList;

    /**
     * The pool of config files.
     *
     * @var ConfigFilePool
     */
    private $configFilePool;

    /**
     * The pool of stream drivers.
     *
     * @var DriverPool
     */
    private $driverPool;

    /**
     * @param DirectoryList $dirList The list of directories
     * @param DriverPool $driverPool The pool of config files
     * @param ConfigFilePool $configFilePool The pool of stream drivers
     */
    public function __construct(
        DirectoryList $dirList,
        DriverPool $driverPool,
        ConfigFilePool $configFilePool
    ) {
        $this->dirList = $dirList;
        $this->configFilePool = $configFilePool;
        $this->driverPool = $driverPool;
    }

    /**
     * Loads the configuration file.
     *
     * @param string $fileKey The file key
     * @return array The configurations array
     * @throws FileSystemException If file can not be read
     * @throws \Exception If file key is not correct
     */
    public function load($fileKey)
    {
        $path = $this->dirList->getPath(DirectoryList::CONFIG);
        $fileDriver = $this->driverPool->getDriver(DriverPool::FILE);
        $filePath = $path . '/' . $this->configFilePool->getPath($fileKey);

        if ($fileDriver->isExists($filePath)) {
            return include $filePath;
        }

        return [];
    }
}
