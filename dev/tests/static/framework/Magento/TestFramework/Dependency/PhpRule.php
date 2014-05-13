<?php
/**
 * Rule for searching php file dependency
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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\TestFramework\Dependency;

class PhpRule implements \Magento\TestFramework\Dependency\RuleInterface
{
    /**
     * List of routers
     *
     * Format: array(
     *  '{Router}' => '{Module_Name}'
     * )
     *
     * @var array
     */
    protected $_mapRouters = array();

    /**
     * List of layout blocks
     *
     * Format: array(
     *  '{Area}' => array(
     *   '{Block_Name}' => array('{Module_Name}' => '{Module_Name}')
     * ))
     *
     * @var array
     */
    protected $_mapLayoutBlocks = array();

    /**
     * Default modules list.
     *
     * @var array
     */
    protected $_defaultModules = array(
        'default' => 'Magento\Install',
        'frontend' => 'Magento\Theme',
        'adminhtml' => 'Magento\Adminhtml'
    );

    /**
     * Constructor
     *
     * @param array $mapRouters
     * @param array $mapLayoutBlocks
     */
    public function __construct(array $mapRouters, array $mapLayoutBlocks)
    {
        $this->_mapRouters = $mapRouters;
        $this->_mapLayoutBlocks = $mapLayoutBlocks;
        $this->_namespaces = implode('|', \Magento\TestFramework\Utility\Files::init()->getNamespaces());
    }

    /**
     * Gets alien dependencies information for current module by analyzing file's contents
     *
     * @param string $currentModule
     * @param string $fileType
     * @param string $file
     * @param string $contents
     * @return array
     */
    public function getDependencyInfo($currentModule, $fileType, $file, &$contents)
    {
        if (!in_array($fileType, array('php', 'template'))) {
            return array();
        }

        $pattern = '~\b(?<class>(?<module>(' . implode(
            '_|',
            \Magento\TestFramework\Utility\Files::init()->getNamespaces()
        ) . '[_\\\\])[a-zA-Z0-9]+)[a-zA-Z0-9_\\\\]*)\b~';

        $dependenciesInfo = array();
        if (preg_match_all($pattern, $contents, $matches)) {
            $matches['module'] = array_unique($matches['module']);
            foreach ($matches['module'] as $i => $referenceModule) {
                $referenceModule = str_replace('_', '\\', $referenceModule);
                if ($currentModule == $referenceModule) {
                    continue;
                }
                $dependenciesInfo[] = array(
                    'module' => $referenceModule,
                    'type' => \Magento\TestFramework\Dependency\RuleInterface::TYPE_HARD,
                    'source' => trim($matches['class'][$i])
                );
            }
        }
        $result = $this->_caseGetUrl($currentModule, $contents);
        if (count($result)) {
            $dependenciesInfo = array_merge($dependenciesInfo, $result);
        }
        $result = $this->_caseLayoutBlock($currentModule, $fileType, $file, $contents);
        if (count($result)) {
            $dependenciesInfo = array_merge($dependenciesInfo, $result);
        }

        return $dependenciesInfo;
    }

    /**
     * Check get URL method
     *
     * Ex.: getUrl('{path}')
     *
     * @param $currentModule
     * @param $contents
     * @return array
     */
    protected function _caseGetUrl($currentModule, &$contents)
    {
        $pattern = '/[\->:]+(?<source>getUrl\([\'"](?<router>[\w\/*]+)[\'"])/';

        $dependencies = array();
        if (!preg_match_all($pattern, $contents, $matches, PREG_SET_ORDER)) {
            return $dependencies;
        }

        foreach ($matches as $item) {
            $router = str_replace('/', '\\', $item['router']);
            if (isset($this->_mapRouters[$router])) {
                $modules = $this->_mapRouters[$router];
                if (!in_array($currentModule, $modules)) {
                    foreach ($modules as $module) {
                        $dependencies[] = array(
                            'module' => $module,
                            'type' => \Magento\TestFramework\Dependency\RuleInterface::TYPE_HARD,
                            'source' => $item['source']
                        );
                    }
                }
            }
        }
        return $dependencies;
    }

    /**
     * Check layout blocks
     *
     * @param $currentModule
     * @param $fileType
     * @param $file
     * @param $contents
     * @return array
     */
    protected function _caseLayoutBlock($currentModule, $fileType, $file, &$contents)
    {
        $pattern = '/[\->:]+(?<source>(?:getBlock|getBlockHtml)\([\'"](?<block>[\w\.\-]+)[\'"]\))/';

        $area = $this->_getAreaByFile($file, $fileType);

        $result = array();
        if (!preg_match_all($pattern, $contents, $matches, PREG_SET_ORDER)) {
            return $result;
        }

        foreach ($matches as $match) {
            if (in_array($match['block'], array('root', 'content'))) {
                continue;
            }
            $check = $this->_checkDependencyLayoutBlock($currentModule, $area, $match['block']);
            $module = isset($check['module']) ? $check['module'] : null;
            if ($module) {
                $result[$module] = array(
                    'type' => \Magento\TestFramework\Dependency\RuleInterface::TYPE_HARD,
                    'source' => $match['source']
                );
            }
        }
        return $this->_getUniqueDependencies($result);
    }

    /**
     * Get area from file path
     *
     * @param $file
     * @param $fileType
     * @return string|null
     */
    protected function _getAreaByFile($file, $fileType)
    {
        if ($fileType == 'php') {
            return null;
        }
        $area = 'default';
        if (preg_match('/\/(?<area>adminhtml|frontend)\//', $file, $matches)) {
            $area = $matches['area'];
        }
        return $area;
    }

    /**
     * Check layout block dependency
     *
     * Return: array(
     *  'module'  // dependent module
     *  'source'  // source text
     * )
     *
     * @param $currentModule
     * @param $area
     * @param $block
     * @return array
     */
    protected function _checkDependencyLayoutBlock($currentModule, $area, $block)
    {
        if (isset($this->_mapLayoutBlocks[$area][$block]) || is_null($area)) {
            // CASE 1: No dependencies
            $modules = array();
            if (is_null($area)) {
                foreach ($this->_mapLayoutBlocks as $blocks) {
                    if (array_key_exists($block, $blocks)) {
                        $modules += $blocks[$block];
                    }
                }
            } else {
                $modules = $this->_mapLayoutBlocks[$area][$block];
            }
            if (isset($modules[$currentModule])) {
                return array('module' => null);
            }
            // CASE 2: Single dependency
            if (1 == count($modules)) {
                return array('module' => current($modules));
            }
            // CASE 3: Default module dependency
            $defaultModule = $this->_getDefaultModuleName($area);
            if (isset($modules[$defaultModule])) {
                return array('module' => $defaultModule);
            }
        }
        // CASE 4: \Exception - Undefined block
        return array();
    }

    /**
     * Retrieve default module name (by area)
     *
     * @param string $area
     * @return null
     */
    protected function _getDefaultModuleName($area = 'default')
    {
        if (isset($this->_defaultModules[$area])) {
            return $this->_defaultModules[$area];
        }
        return null;
    }

    /**
     * Retrieve unique dependencies
     *
     * @param array $dependencies
     * @return array
     */
    protected function _getUniqueDependencies($dependencies = array())
    {
        $result = array();
        foreach ($dependencies as $module => $value) {
            $result[] = array('module' => $module, 'type' => $value['type'], 'source' => $value['source']);
        }
        return $result;
    }
}
