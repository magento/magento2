<?php
/**
 * Module configuration file reader
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Module\Dir;

class Reader
{
    /**
     * Module directories that were set explicitly
     *
     * @var array
     */
    protected $_customModuleDirs = array();

    /**
     * Directory registry
     *
     * @var \Magento\Module\Dir
     */
    protected $_moduleDirs;

    /**
     * Modules configuration provider
     *
     * @var \Magento\Module\ModuleListInterface
     */
    protected $_modulesList;

    /**
     * @param \Magento\Module\Dir $moduleDirs
     * @param \Magento\Module\ModuleListInterface $moduleList
     */
    public function __construct(
        \Magento\Module\Dir $moduleDirs,
        \Magento\Module\ModuleListInterface $moduleList
    ) {
        $this->_moduleDirs = $moduleDirs;
        $this->_modulesList = $moduleList;
    }

    /**
     * Go through all modules and find configuration files of active modules
     *
     * @param $filename
     * @return array
     */
    public function getConfigurationFiles($filename)
    {
        $result = array();
        foreach (array_keys($this->_modulesList->getModules()) as $moduleName) {
            $file = $this->getModuleDir('etc', $moduleName) . DIRECTORY_SEPARATOR . $filename;
            if (file_exists($file)) {
                $result[] = $file;
            }
        }
        return $result;
    }

    /**
     * Get module directory by directory type
     *
     * @param string $type
     * @param string $moduleName
     * @return string
     */
    public function getModuleDir($type, $moduleName)
    {
        if (isset($this->_customModuleDirs[$moduleName][$type])) {
            return $this->_customModuleDirs[$moduleName][$type];
        }
        return $this->_moduleDirs->getDir($moduleName, $type);
    }

    /**
     * Set path to the corresponding module directory
     *
     * @param string $moduleName
     * @param string $type directory type (etc, controllers, locale etc)
     * @param string $path
     */
    public function setModuleDir($moduleName, $type, $path)
    {
        $this->_customModuleDirs[$moduleName][$type] = $path;
    }
}
