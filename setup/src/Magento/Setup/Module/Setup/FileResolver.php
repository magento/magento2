<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Module\Setup;

use Magento\Framework\App\Filesystem\DirectoryList;
use Zend\Stdlib\Glob;

class FileResolver
{
    /**
     * Magento application's DirectoryList
     *
     * @var DirectoryList
     */
    private $directoryList;

    /**
     * Default Constructor
     *
     * @param DirectoryList $directoryList
     */
    public function __construct(DirectoryList $directoryList)
    {
        $this->directoryList = $directoryList;
    }

    /**
     * Get SQL setup files by pattern
     *
     * @param string $moduleName
     * @param string $fileNamePattern
     * @return array
     */
    public function getSqlSetupFiles($moduleName, $fileNamePattern = '*.php')
    {
        $modulePath = str_replace('_', '/', $moduleName);
        $pattern = $this->directoryList->getPath(DirectoryList::MODULES)
            . '/' . $modulePath . '/sql/*/' . $fileNamePattern;
        return Glob::glob($pattern, Glob::GLOB_BRACE);
    }

    /**
     * Get Resource Code by Module Name
     *
     * @param string $moduleName
     * @return string
     */
    public function getResourceCode($moduleName)
    {
        $sqlResources  = [];
        $dataResources = [];
        $modulePath = str_replace('_', '/', $moduleName);

        // Collect files by /app/code/{modulePath}/sql/*/ pattern
        $pattern = $this->directoryList->getPath(DirectoryList::MODULES) . '/' . $modulePath . '/sql/*';
        $resourceDirs = Glob::glob($pattern, Glob::GLOB_ONLYDIR);
        if (!empty($resourceDirs)) {
            foreach ($resourceDirs as $resourceDir) {
                $sqlResources[] = basename($resourceDir);
            }
        }

        // Collect files by /app/code/{modulePath}/data/*/ pattern
        $pattern = $this->directoryList->getPath(DirectoryList::MODULES) . '/' . $modulePath . '/data/*';
        $resourceDirs = Glob::glob($pattern, Glob::GLOB_ONLYDIR);
        if (!empty($resourceDirs)) {
            foreach ($resourceDirs as $resourceDir) {
                $dataResources[] = basename($resourceDir);
            }
        }

        $resources = array_unique(array_merge($sqlResources, $dataResources));
        return array_shift($resources);
    }
}
