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
 * @copyright Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\App\Module;

class ResourceResolver implements \Magento\App\Module\ResourceResolverInterface
{
    /**
     * @var \Magento\Core\Model\Config\Modules\Reader
     */
    protected $_moduleReader;

    /**
     * Map that contains cached resources per module
     *
     * @var array
     */
    protected $_moduleResources = array();

    /**
     * @param \Magento\Core\Model\Config\Modules\Reader $moduleReader
     */
    public function __construct(\Magento\Core\Model\Config\Modules\Reader $moduleReader)
    {
        $this->_moduleReader = $moduleReader;
    }

    /**
     * Retrieve the list of resources declared by the given module
     *
     * @param string $moduleName
     * @return array
     */
    public function getResourceList($moduleName)
    {
        if (!isset($this->_moduleResources[$moduleName])) {
            // Process sub-directories within modules sql directory
            $moduleSqlDir = $this->_moduleReader->getModuleDir('sql', $moduleName);
            $sqlResources = array();
            foreach (glob($moduleSqlDir . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR) as $resourceDir) {
                $sqlResources[] = basename($resourceDir);
            }
            $moduleDataDir = $this->_moduleReader->getModuleDir('data', $moduleName);
            // Process sub-directories within modules data directory
            $dataResources = array();
            foreach (glob($moduleDataDir . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR) as $resourceDir) {
                $dataResources[] = basename($resourceDir);
            }
            $this->_moduleResources[$moduleName] = array_unique(array_merge(
                $sqlResources,
                $dataResources
            ));
        }
        return $this->_moduleResources[$moduleName];
    }
}
