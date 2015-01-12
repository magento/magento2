<?php
/**
 * Resource resolver is used to retrieve a list of resources declared by module
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Module;

use Magento\Framework\Module\Dir\Reader;

class ResourceResolver implements \Magento\Framework\Module\ResourceResolverInterface
{
    /**
     * @var Reader
     */
    protected $_moduleReader;

    /**
     * Map that contains cached resources per module
     *
     * @var array
     */
    protected $_moduleResources = [];

    /**
     * @param Reader $moduleReader
     */
    public function __construct(Reader $moduleReader)
    {
        $this->_moduleReader = $moduleReader;
    }

    /**
     * Retrieve the list of resources declared by the given module
     *
     * @param string $moduleName
     * @return string[]
     */
    public function getResourceList($moduleName)
    {
        if (!isset($this->_moduleResources[$moduleName])) {
            // Process sub-directories within modules sql directory
            $moduleSqlDir = $this->_moduleReader->getModuleDir('sql', $moduleName);
            $sqlResources = [];
            $resourceDirs = glob($moduleSqlDir . '/*', GLOB_ONLYDIR);
            if (!empty($resourceDirs)) {
                foreach ($resourceDirs as $resourceDir) {
                    $sqlResources[] = basename($resourceDir);
                }
            }
            $moduleDataDir = $this->_moduleReader->getModuleDir('data', $moduleName);
            // Process sub-directories within modules data directory
            $dataResources = [];
            $resourceDirs = glob($moduleDataDir . '/*', GLOB_ONLYDIR);
            if (!empty($resourceDirs)) {
                foreach ($resourceDirs as $resourceDir) {
                    $dataResources[] = basename($resourceDir);
                }
            }
            $this->_moduleResources[$moduleName] = array_unique(array_merge($sqlResources, $dataResources));
        }
        return $this->_moduleResources[$moduleName];
    }
}
