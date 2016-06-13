<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Code;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\Filesystem\Directory\WriteFactory;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\App\DeploymentConfig\Writer\PhpFormatter;

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
     * @deprecated
     * @see \Magento\Framework\Code\GeneratedFiles::cleanGeneratedFiles
     */
    public function regenerate()
    {
        if ($this->write->isExist(self::REGENERATE_FLAG)) {
            //TODO: to be removed in scope of MAGETWO-53476
            //clean cache
            $deploymentConfig = $this->directoryList->getPath(DirectoryList::CONFIG);
            $configPool = new ConfigFilePool();
            $envPath = $deploymentConfig . '/' . $configPool->getPath(ConfigFilePool::APP_ENV);
            if ($this->write->isExist($this->write->getRelativePath($envPath))) {
                $this->saveCacheStatus($envPath);
            }
            //TODO: Till here
            $cachePath = $this->write->getRelativePath($this->directoryList->getPath(DirectoryList::CACHE));
            $generationPath = $this->write->getRelativePath($this->directoryList->getPath(DirectoryList::GENERATION));
            $diPath = $this->write->getRelativePath($this->directoryList->getPath(DirectoryList::DI));

            if ($this->write->isDirectory($generationPath)) {
                $this->write->delete($generationPath);
            }
            if ($this->write->isDirectory($diPath)) {
                $this->write->delete($diPath);
            }
            if ($this->write->isDirectory($cachePath)) {
                $this->write->delete($cachePath);
            }
            //add to queue

            $this->write->delete(self::REGENERATE_FLAG);
        }
    }


    /**
     * Clean var/generation, var/di and var/cache
     *
     * @return void
     */
    public function cleanGeneratedFiles()
    {
        if ($this->write->isExist(self::REGENERATE_FLAG)) {

            $cacheStatus = [];

            //TODO: to be removed in scope of MAGETWO-53476
            $deploymentConfig = $this->directoryList->getPath(DirectoryList::CONFIG);
            $configPool = new ConfigFilePool();
            $envPath = $deploymentConfig . '/' . $configPool->getPath(ConfigFilePool::APP_ENV);
            if ($this->write->isExist($this->write->getRelativePath($envPath))) {
                $cacheStatus = $this->getCacheStatus();
                $this->disableAllCacheTypes();
            }
            //TODO: Till here

            $cachePath = $this->write->getRelativePath($this->directoryList->getPath(DirectoryList::CACHE));
            $generationPath = $this->write->getRelativePath($this->directoryList->getPath(DirectoryList::GENERATION));
            $diPath = $this->write->getRelativePath($this->directoryList->getPath(DirectoryList::DI));

            // Clean var/generation dir
            if ($this->write->isDirectory($generationPath)) {
                $this->write->delete($generationPath);
            }

            // Clean var/di
            if ($this->write->isDirectory($diPath)) {
                $this->write->delete($diPath);
            }

            // Clean var/cache
            if ($this->write->isDirectory($cachePath)) {
                $this->write->delete($cachePath);
            }
            $this->write->delete(self::REGENERATE_FLAG);
            $this->restoreCacheStatus($cacheStatus);
        }
    }

    /**
     * Create flag for cleaning up var/generation, var/di and var/cache directories for subsequent
     * regeneration of this content
     *
     * @return void
     */
    public function requestRegeneration()
    {
        $this->write->touch(self::REGENERATE_FLAG);
    }

    /**
     * Read Cache types from env.php and write to a json file.
     *
     * @param string $envPath
     * @return void
     *
     */
    private function saveCacheStatus($envPath)
    {
        $cacheData = include $envPath;

        if (isset($cacheData['cache_types'])) {
            $enabledCacheTypes = $cacheData['cache_types'];
            $enabledCacheTypes = array_filter($enabledCacheTypes, function ($value) {
                return $value;
            });
            if (!empty($enabledCacheTypes)) {
                $varDir = $this->directoryList->getPath(DirectoryList::VAR_DIR);
                $this->write->writeFile(
                    $this->write->getRelativePath($varDir) . '/.cachestates.json',
                    json_encode($enabledCacheTypes)
                );
                $cacheTypes = array_keys($cacheData['cache_types']);

                foreach ($cacheTypes as $cacheType) {
                    $cacheData['cache_types'][$cacheType] = 0;
                }

                $formatter = new PhpFormatter();
                $contents = $formatter->format($cacheData);

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
     * Reads Cache configuration from env.php and returns the 'cache_types' key data which is the current
     * cache status.
     *
     * @return array
     */
    private function getCacheStatus()
    {
        $cacheStatus = [];
        if (empty($envPath)) {
            $envPath = $this->getEnvPath();
        }

        if ($this->write->isExist($this->write->getRelativePath($envPath) &&
            $this->write->isReadable($this->write->getRelativePath($envPath)))) {
            $envData = include $envPath;
            if (isset($envData['cache_types'])) {
                $cacheStatus = $envData['cache_types'];
            }
        }
        return $cacheStatus;
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

    /**
     * restore the cacache setting in env.php
     * TODO: to be removed in scope of MAGETWO-53476
     *
     * @param array
     *
     * @return void
     */
    private function restoreCacheStatus($cacheStatus)
    {
        if (empty($cacheStatus)) {
            return;
        }
        $envPath = $this->getEnvPath();
        if ($this->write->isExist($this->write->getRelativePath($envPath) &&
            $this->write->isReadable($this->write->getRelativePath($envPath)))) {
            $envData = include $envPath;
            foreach ($cacheStatus as $cacheType => $state) {
                if (isset($envData['cache_types'][$cacheType])) {
                    $envData['cache_types'][$cacheType] = $state;
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
