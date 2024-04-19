<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\DeploymentConfig;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\RuntimeException;
use Magento\Framework\Filesystem\DriverPool;
use Magento\Framework\Phrase;

/**
 * Deployment configuration reader.
 * Loads the merged configuration from config files.
 * @see FileReader The reader for specific configuration file
 */
class Reader
{
    /**
     * @var DirectoryList
     */
    private $dirList;

    /**
     * @var ConfigFilePool
     */
    private $configFilePool;

    /**
     * @var DriverPool
     */
    private $driverPool;

    /**
     * Configuration file names
     *
     * @var array
     */
    private $files;

    /**
     * Constructor
     *
     * @param DirectoryList $dirList
     * @param DriverPool $driverPool
     * @param ConfigFilePool $configFilePool
     * @param null|string $file
     * @throws \InvalidArgumentException
     */
    public function __construct(
        DirectoryList $dirList,
        DriverPool $driverPool,
        ConfigFilePool $configFilePool,
        $file = null
    ) {
        $this->dirList = $dirList;
        $this->configFilePool = $configFilePool;
        $this->driverPool = $driverPool;
        if (null !== $file) {
            if (!preg_match('/^[a-z\d\.\-]+\.php$/i', $file)) {
                throw new \InvalidArgumentException("Invalid file name: {$file}");
            }
            $this->files = [$file];
        } else {
            $this->files = $this->configFilePool->getPaths();
        }
    }

    /**
     * Gets the file name
     *
     * @return array
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * Method loads merged configuration within all configuration files.
     * To retrieve specific file configuration, use FileReader.
     * $fileKey option is deprecated since version 2.2.0.
     *
     * @param string $fileKey The file key (deprecated)
     * @return array
     * @throws FileSystemException If file can not be read
     * @throws RuntimeException If file is invalid
     * @throws \Exception If file key is not correct
     * @see FileReader
     */
    public function load($fileKey = null)
    {
        static $cache = [];

        $path = $this->dirList->getPath(DirectoryList::CONFIG);
        $fileDriver = $this->driverPool->getDriver(DriverPool::FILE);
        $result = [];

        if ($fileKey) {
            $filePath = $path . '/' . $this->configFilePool->getPath($fileKey);
            if (!isset($cache[$filePath])) {  // Check if the file data is already cached
                if ($fileDriver->isExists($filePath)) {
                    $fileData = include $filePath;
                    if (!is_array($fileData)) {
                        throw new RuntimeException(new Phrase("Invalid configuration file: '%1'", [$filePath]));
                    }
                    $cache[$filePath] = $fileData;  // Cache the file data
                } else {
                    $cache[$filePath] = null;  // Cache that the file does not exist or is invalid
                }
            }
            $result = $cache[$filePath] ?: [];
        } else {
            $configFiles = $this->getFiles();
            foreach ($configFiles as $file) {
                $configFile = $path . '/' . $file;
                if (!isset($cache[$configFile])) {  // Check cache first
                    if ($fileDriver->isExists($configFile)) {
                        $fileData = include $configFile;
                        if (!is_array($fileData)) {
                            throw new RuntimeException(new Phrase("Invalid configuration file: '%1'", [$configFile]));
                        }
                        $cache[$configFile] = $fileData;  // Cache the file data
                    } else {
                        $cache[$configFile] = null;  // Cache that the file does not exist or is invalid
                    }
                }
                if ($cache[$configFile]) {
                    $result = array_replace_recursive($result, $cache[$configFile]);
                }
            }
        }
        return $result ?: [];
    }
}
