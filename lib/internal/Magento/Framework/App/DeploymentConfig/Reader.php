<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\DeploymentConfig;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Config\File\ConfigFilePool;

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
     * Configuration file names
     *
     * @var array
     */
    private $files;

    /**
     * Constructor
     *
     * @param DirectoryList $dirList
     * @param ConfigFilePool $configFilePool
     * @param null|string $file
     * @throws \InvalidArgumentException
     */
    public function __construct(DirectoryList $dirList, ConfigFilePool $configFilePool, $file = null)
    {
        $this->dirList = $dirList;
        $this->configFilePool = $configFilePool;
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
        if ($fileKey) {
            $result = @include $path . '/' . $this->configFilePool->getPath($fileKey);
        } else {
            $configFiles = $this->configFilePool->getPaths();
            $result = [];
            foreach (array_keys($configFiles) as $fileKey) {
                $configFile = $path . '/' . $this->configFilePool->getPath($fileKey);
                $fileData = @include $configFile;
                if (!empty($fileData)) {
                    $result = array_merge_recursive($result, $fileData);
                    $this->flattenParams($result);
                }
            }
        }
        return $result ?: [];
    }

    /**
     * Convert associative array of arbitrary depth to a flat associative array with concatenated key path as keys
     * each level of array is accessible by path key
     *
     * @param array $params
     * @param string $path
     * @return array
     * @throws \Exception
     */
    public function flattenParams(array $params, $path = null)
    {
        $cache = [];

        foreach ($params as $key => $param) {
            if ($path) {
                $newPath = $path . '/' . $key;
            } else {
                $newPath = $key;
            }
            if (isset($cache[$newPath]) || is_int($key)) {
                throw new \Exception("Key collision {$newPath} is already defined.");
            }
            $cache[$newPath] = $param;
            if (is_array($param)) {
                $cache = array_merge($cache, $this->flattenParams($param, $newPath));
            }
        }

        return $cache;
    }
}
