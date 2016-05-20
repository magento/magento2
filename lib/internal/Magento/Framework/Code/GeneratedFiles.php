<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Code;

use Magento\Framework\Config\Data\ConfigData;
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
     */
    public function regenerate()
    {
        if ($this->write->isExist(self::REGENERATE_FLAG)) {
            //clean cache
            $deploymentConfig = $this->directoryList->getPath(DirectoryList::CONFIG);
            $configPool = new ConfigFilePool();
            $envPath = $deploymentConfig . '/' . $configPool->getPath(ConfigFilePool::APP_ENV);
            if ($this->write->isExist($this->write->getRelativePath($envPath))) {
                $this->saveCacheStatus($envPath);
            }
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
     * Read Cache types from env.php and write to a json file.
     *
     * @param string $envPath
     * @return void
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
     * Create flag for regeneration of code and di
     *
     * @return void
     */
    public function requestRegeneration()
    {
        $this->write->touch(self::REGENERATE_FLAG);
    }
}
