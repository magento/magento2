<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\DeploymentConfig;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\Filesystem\DriverPool;

/**
 * Deployment configuration reader
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
     * Loads the configuration file
     *
     * @param string $fileKey
     * @return array
     * @throws \Exception
     */
    public function load($fileKey = null)
    {
        $path = $this->dirList->getPath(DirectoryList::CONFIG);
        $fileDriver = $this->driverPool->getDriver(DriverPool::FILE);
        $result = [];
        if ($fileKey) {
            $filePath = $path . '/' . $this->configFilePool->getPath($fileKey);
            if ($fileDriver->isExists($filePath)) {
                $result = include $filePath;
            }
        } else {
            $configFiles = $this->configFilePool->getPaths();
            $allFilesData = [];
            $result = [];
            foreach (array_keys($configFiles) as $fileKey) {
                $configFile = $path . '/' . $this->configFilePool->getPath($fileKey);
                if ($fileDriver->isExists($configFile)) {
                    $fileData = include $configFile;
                } else {
                    continue;
                }
                $allFilesData[$configFile] = $fileData;
                if (!empty($fileData)) {
                    $intersection = array_intersect_key($result, $fileData);
                    if (!empty($intersection)) {
                        $displayMessage = $this->findFilesWithKeys(array_keys($intersection), $allFilesData);
                        throw new \Exception(
                            "Key collision! The following keys occur in multiple config files:"
                            . PHP_EOL . $displayMessage
                        );
                    }
                    $result = array_merge($result, $fileData);
                }
            }
        }
        return $result ?: [];
    }

    /**
     * Finds list of files that has the key
     *
     * @param array $keys
     * @param array $allFilesData
     * @return string
     */
    private function findFilesWithKeys(array $keys, array $allFilesData)
    {
        $displayMessage = '';
        foreach ($keys as $key) {
            $foundConfigFiles = [];
            foreach ($allFilesData as $fileName => $fileValues) {
                if (isset($fileValues[$key])) {
                    $foundConfigFiles[] = $fileName;
                }
            }
            $displayMessage .= 'Key "' . $key . '" found in ' . implode(', ', $foundConfigFiles) . PHP_EOL;
        }
        return $displayMessage;
    }
}
