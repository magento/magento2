<?php
/**
 * Sorted modules config
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
class Mage_Core_Model_Config_Modules_Sorted extends Mage_Core_Model_Config_Base
{
    /**
     * Types of dependencies between modules
     */
    const DEPENDENCY_TYPE_SOFT = 'soft';
    const DEPENDENCY_TYPE_HARD = 'hard';

    /**
     * Constructor
     *
     * @param Mage_Core_Model_Config_Base $modulesConfig Modules configuration merged from the config files
     * @param array $allowedModules When not empty, defines modules to be taken into account
     */
    public function __construct(Mage_Core_Model_Config_Base $modulesConfig, array $allowedModules = array())
    {
        // initialize empty modules configuration
        parent::__construct('<config><modules/></config>');

        $moduleDependencies = $this->_loadModuleDependencies($modulesConfig, $allowedModules);

        $this->_checkModuleRequirements($moduleDependencies);

        $moduleDependencies = $this->_sortModuleDependencies($moduleDependencies);

        // create sorted configuration
        foreach ($modulesConfig->getNode()->children() as $nodeName => $node) {
            if ($nodeName != 'modules') {
                $this->getNode()->appendChild($node);
            }
        }
        foreach ($moduleDependencies as $moduleInfo) {
            $node = $modulesConfig->getNode('modules/' . $moduleInfo['module']);
            $this->getNode('modules')->appendChild($node);
        }
    }

    /**
     * Load dependencies for active & allowed modules into an array structure
     *
     * @param Mage_Core_Model_Config_Base $modulesConfig
     * @param array $allowedModules
     * @return array
     */
    protected function _loadModuleDependencies(Mage_Core_Model_Config_Base $modulesConfig, array $allowedModules)
    {
        $result = array();
        foreach ($modulesConfig->getNode('modules')->children() as $moduleName => $moduleNode) {
            $isModuleActive = 'true' === (string)$moduleNode->active;
            $isModuleAllowed = empty($allowedModules) || in_array($moduleName, $allowedModules);
            if (!$isModuleActive || !$isModuleAllowed) {
                continue;
            }
            $dependencies = array();
            if ($moduleNode->depends) {
                /** @var $dependencyNode Varien_Simplexml_Element */
                foreach ($moduleNode->depends->children() as $dependencyNode) {
                    $dependencyModuleName = $dependencyNode->getName();
                    $dependencies[$dependencyModuleName] = $this->_getDependencyType($dependencyNode);
                }
            }
            $result[$moduleName] = array(
                'module'       => $moduleName,
                'dependencies' => $dependencies,
            );
        }
        return $result;
    }

    /**
     * Determine dependency type from XML node that defines module dependency
     *
     * @param Varien_Simplexml_Element $dependencyNode
     * @return string
     * @throws UnexpectedValueException
     */
    protected function _getDependencyType(Varien_Simplexml_Element $dependencyNode)
    {
        $result = $dependencyNode->getAttribute('type') ?: self::DEPENDENCY_TYPE_HARD;
        if (!in_array($result, array(self::DEPENDENCY_TYPE_HARD, self::DEPENDENCY_TYPE_SOFT))) {
            $dependencyNodeXml = trim($dependencyNode->asNiceXml());
            throw new UnexpectedValueException(
                "Unknown module dependency type '$result' in declaration '$dependencyNodeXml'."
            );
        }
        return $result;
    }

    /**
     * Check whether module requirements are fulfilled
     *
     * @param array $moduleDependencies
     * @throws Magento_Exception
     */
    protected function _checkModuleRequirements(array $moduleDependencies)
    {
        foreach ($moduleDependencies as $moduleName => $moduleInfo) {
            foreach ($moduleInfo['dependencies'] as $relatedModuleName => $dependencyType) {
                $relatedModuleActive = isset($moduleDependencies[$relatedModuleName]);
                if (!$relatedModuleActive && $dependencyType == self::DEPENDENCY_TYPE_HARD) {
                    throw new Magento_Exception("Module '$moduleName' requires module '$relatedModuleName'.");
                }
            }
        }
    }

    /**
     * Sort modules until dependent modules go after ones they depend on
     *
     * @param array $moduleDependencies
     * @return array
     */
    protected function _sortModuleDependencies(array $moduleDependencies)
    {
        // add indirect dependencies
        foreach ($moduleDependencies as $moduleName => &$moduleInfo) {
            $moduleInfo['dependencies'] = $this->_getAllDependencies($moduleDependencies, $moduleName);
        }
        unset($moduleInfo);

        // "bubble sort" modules until dependent modules go after ones they depend on
        $moduleDependencies = array_values($moduleDependencies);
        $size = count($moduleDependencies) - 1;
        for ($i = $size; $i >= 0; $i--) {
            for ($j = $size; $i < $j; $j--) {
                if (isset($moduleDependencies[$i]['dependencies'][$moduleDependencies[$j]['module']])) {
                    $tempValue              = $moduleDependencies[$i];
                    $moduleDependencies[$i] = $moduleDependencies[$j];
                    $moduleDependencies[$j] = $tempValue;
                }
            }
        }

        return $moduleDependencies;
    }

    /**
     * Recursively compute all dependencies and detect circular ones
     *
     * @param array $moduleDependencies
     * @param string $moduleName
     * @param array $usedModules Keep track of used modules to detect circular dependencies
     * @return array
     * @throws Magento_Exception
     */
    protected function _getAllDependencies(array $moduleDependencies, $moduleName, $usedModules = array())
    {
        $usedModules[] = $moduleName;
        $result = $moduleDependencies[$moduleName]['dependencies'];
        foreach (array_keys($result) as $relatedModuleName) {
            if (in_array($relatedModuleName, $usedModules)) {
                throw new Magento_Exception(
                    "Module '$moduleName' cannot depend on '$relatedModuleName' since it creates circular dependency."
                );
            }
            if (empty($moduleDependencies[$relatedModuleName])) {
                continue;
            }
            $relatedDependencies = $this->_getAllDependencies($moduleDependencies, $relatedModuleName, $usedModules);
            $result = array_merge($result, $relatedDependencies);
        }
        return $result;
    }
}
