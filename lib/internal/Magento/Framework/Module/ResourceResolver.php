<?php
/**
 * Resource resolver is used to retrieve a list of resources declared by module
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
    protected $_moduleResources = array();

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
            $sqlResources = array();
            $resourceDirs = glob($moduleSqlDir . '/*', GLOB_ONLYDIR);
            if (!empty($resourceDirs)) {
                foreach ($resourceDirs as $resourceDir) {
                    $sqlResources[] = basename($resourceDir);
                }
            }
            $moduleDataDir = $this->_moduleReader->getModuleDir('data', $moduleName);
            // Process sub-directories within modules data directory
            $dataResources = array();
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
