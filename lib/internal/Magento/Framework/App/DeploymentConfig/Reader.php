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
     * @param string $configFile
     * @throws \Exception
     * @return array
     */
    public function load($configFile = null)
    {
        $path = $this->dirList->getPath(DirectoryList::CONFIG);
        if ($configFile) {
            $result = @include $path . '/' . $this->configFilePool->getPath($configFile);
        } else {
            $configFiles = $this->configFilePool->getPaths();
            $result = [];
            foreach (array_keys($configFiles) as $fileKey) {
                $configFile = $path . '/' . $this->configFilePool->getPath($fileKey);
                $fileData = @include $configFile;
                if (empty($fileData)) {
                    $result = array_merge($result, []);
                } elseif (empty(array_intersect_key($result, $fileData))) {
                    $result = array_replace_recursive($result, $fileData);
                } else {
                    throw new \Exception('Duplicate keys are present');
                }
            }
        }
        return $result ?: [];
    }
}
