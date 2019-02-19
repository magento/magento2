<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Code;

use Magento\Framework\App\DeploymentConfig\Writer\PhpFormatter;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\Filesystem\Directory\WriteFactory;
use Magento\Framework\Filesystem\Directory\WriteInterface;

/**
 * Regenerates generated code and DI configuration
 */
class GeneratedFiles
{
    /**
     * Separator literal to assemble timer identifier from timer names
     */
    const REGENERATE_FLAG = '/var/.regenerate';

    /**
     * @var DirectoryList
     */
    private $directoryList;

    /**
     * @var WriteInterface
     */
    private $write;

    /**
     * Constructor
     *
     * @param DirectoryList $directoryList
     * @param WriteFactory $writeFactory
     */
    public function __construct(DirectoryList $directoryList, WriteFactory $writeFactory)
    {
        $this->directoryList = $directoryList;
        $this->write = $writeFactory->create(BP);
    }

    /**
     * Clean generated code and DI configuration
     *
     * @return void
     *
     * @deprecated 100.1.0
     * @see \Magento\Framework\Code\GeneratedFiles::cleanGeneratedFiles
     */
    public function regenerate()
    {
        $this->cleanGeneratedFiles();
    }

    /**
     * Clean generated/code, generated/metadata and var/cache
     *
     * @return void
     */
    public function cleanGeneratedFiles()
    {
        if ($this->write->isExist(self::REGENERATE_FLAG)) {
            $enabledCacheTypes = [];

            //TODO: to be removed in scope of MAGETWO-53476
            $deploymentConfig = $this->directoryList->getPath(DirectoryList::CONFIG);
            $configPool = new ConfigFilePool();
            $envPath = $deploymentConfig . '/' . $configPool->getPath(ConfigFilePool::APP_ENV);
            if ($this->write->isExist($this->write->getRelativePath($envPath))) {
                $enabledCacheTypes = $this->getEnabledCacheTypes();
                $this->disableAllCacheTypes();
            }
            //TODO: Till here

            $cachePath = $this->write->getRelativePath($this->directoryList->getPath(DirectoryList::CACHE));
            $generationPath = $this->write->getRelativePath(
                $this->directoryList->getPath(DirectoryList::GENERATED_CODE)
            );
            $diPath = $this->write->getRelativePath($this->directoryList->getPath(DirectoryList::GENERATED_METADATA));

            // Clean generated/code dir
            if ($this->write->isDirectory($generationPath)) {
                $this->write->delete($generationPath);
            }

            // Clean generated/metadata
            if ($this->write->isDirectory($diPath)) {
                $this->write->delete($diPath);
            }

            // Clean var/cache
            if ($this->write->isDirectory($cachePath)) {
                $this->write->delete($cachePath);
            }
            $this->write->delete(self::REGENERATE_FLAG);
            $this->enableCacheTypes($enabledCacheTypes);
        }
    }

    /**
     * Create flag for cleaning up generated/code, generated/metadata and var/cache directories for subsequent
     * regeneration of this content
     *
     * @return void
     */
    public function requestRegeneration()
    {
        $this->write->touch(self::REGENERATE_FLAG);
    }

    /**
     * Reads Cache configuration from env.php and returns indexed array containing all the enabled cache types.
     *
     * @return string[]
     */
    private function getEnabledCacheTypes()
    {
        $enabledCacheTypes = [];
        $envPath = $this->getEnvPath();
        if ($this->write->isReadable($this->write->getRelativePath($envPath))) {
            $envData = include $envPath;
            if (isset($envData['cache_types'])) {
                $cacheStatus = $envData['cache_types'];
                $enabledCacheTypes = array_filter($cacheStatus, function ($value) {
                    return $value;
                });
                $enabledCacheTypes = array_keys($enabledCacheTypes);
            }
        }
        return $enabledCacheTypes;
    }

    /**
     * Returns path to env.php file
     *
     * @return string
     * @throws \Exception
     */
    private function getEnvPath()
    {
        $deploymentConfig = $this->directoryList->getPath(DirectoryList::CONFIG);
        $configPool = new ConfigFilePool();
        $envPath = $deploymentConfig . '/' . $configPool->getPath(ConfigFilePool::APP_ENV);
        return $envPath;
    }

    /**
     * Disables all cache types by updating env.php.
     *
     * @return void
     */
    private function disableAllCacheTypes()
    {
        $envPath = $this->getEnvPath();
        if ($this->write->isWritable($this->write->getRelativePath($envPath))) {
            $envData = include $envPath;

            if (isset($envData['cache_types'])) {
                $cacheTypes = array_keys($envData['cache_types']);

                foreach ($cacheTypes as $cacheType) {
                    $envData['cache_types'][$cacheType] = 0;
                }

                $formatter = new PhpFormatter();
                $contents = $formatter->format($envData);

                $this->write->writeFile($this->write->getRelativePath($envPath), $contents);
                if (function_exists('opcache_invalidate')) {
                    opcache_invalidate(
                        $this->write->getAbsolutePath($envPath)
                    );
                }
            }
        }
    }

    /**
     * Enables appropriate cache types in app/etc/env.php based on the passed in $cacheTypes array
     * TODO: to be removed in scope of MAGETWO-53476
     *
     * @param string[] $cacheTypes
     * @return void
     */
    private function enableCacheTypes($cacheTypes)
    {
        if (empty($cacheTypes)) {
            return;
        }
        $envPath = $this->getEnvPath();
        if ($this->write->isReadable($this->write->getRelativePath($envPath))) {
            $envData = include $envPath;
            foreach ($cacheTypes as $cacheType) {
                if (isset($envData['cache_types'][$cacheType])) {
                    $envData['cache_types'][$cacheType] = 1;
                }
            }

            $formatter = new PhpFormatter();
            $contents = $formatter->format($envData);

            $this->write->writeFile($this->write->getRelativePath($envPath), $contents);
            if (function_exists('opcache_invalidate')) {
                opcache_invalidate($this->write->getAbsolutePath($envPath));
            }
        }
    }
}
