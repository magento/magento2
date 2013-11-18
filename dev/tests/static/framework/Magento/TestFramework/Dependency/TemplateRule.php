<?php
/**
 * Rule for searching dependencies in layout files
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
 * @category    Magento
 * @package     Magento
 * @subpackage  static_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\TestFramework\Dependency;

class TemplateRule implements \Magento\TestFramework\Dependency\RuleInterface
{
    /**
     * Cases to search dependencies
     *
     * @var array
     */
    protected $_cases = array(
        '_caseModelSingleton',
        '_caseHelper',
        '_caseCreateBlock',
        '_caseConstant',
        '_caseAddFile',
        '_caseGetUrl',
        '_caseLayoutBlock',
    );

    /**
     * Default modules list.
     *
     * @var array
     */
    protected $_defaultModules = array(
        'default'   => 'Magento_Install',
        'frontend'  => 'Magento_Page',
        'adminhtml' => 'Magento_Adminhtml',
    );

    /**
     * Namespaces to analyze
     *
     * Format: {Namespace}|{Namespace}|...
     *
     * @var string
     */
    protected $_namespaces;

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
     * List of exceptions
     *
     * Format: array(
     *  '{Exception_Type}' => '{Source}'
     * )
     *
     * @var array
     */
    protected $_exceptions = array();

    /**
     * Display exceptions
     */
    const EXCEPTION_ALLOWED = false;

    /**
     * Unknown layout block
     */
    const EXCEPTION_TYPE_UNKNOWN_BLOCK = 'UNKNOWN_BLOCK';

    /**
     * Undefined dependency
     */
    const EXCEPTION_TYPE_UNDEFINED_DEPENDENCY = 'UNDEFINED_DEPENDENCY';

    /**
     * Constructor
     */
    public function __construct()
    {
        $args = func_get_args();
        if (count($args)) {
            if (isset($args[0]['mapRouters'])) {
                $this->_mapRouters = $args[0]['mapRouters'];
            }
            if (isset($args[0]['mapLayoutBlocks'])) {
                $this->_mapLayoutBlocks = $args[0]['mapLayoutBlocks'];
            }
        }

        $this->_namespaces = implode('|', \Magento\TestFramework\Utility\Files::init()->getNamespaces());
    }

    /**
     * Retrieve dependencies information for current module
     *
     * @param string $currentModule
     * @param string $fileType
     * @param string $file
     * @param string $contents
     * @return array
     */
    public function getDependencyInfo($currentModule, $fileType, $file, &$contents)
    {
        if ('template' != $fileType) {
            return array();
        }

        $this->_exceptions = array();

        $dependencies = array();
        foreach ($this->_cases as $case) {
            if (method_exists($this, $case)) {
                $result = $this->$case($currentModule, $fileType, $file, $contents);
                if (count($result)) {
                    $dependencies = array_merge($dependencies, $result);
                }
            }
        }
        return array_merge($dependencies, $this->_applyExceptions());
    }

    /**
     * Apply exceptions
     *
     * @return array
     */
    protected function _applyExceptions()
    {
        if (!self::EXCEPTION_ALLOWED) {
            return array();
        }

        $result = array();
        foreach ($this->_exceptions as $type => $source) {
            if (is_array($source)) {
                $source = array_keys($source);
            }
            $result[] = array(
                'exception' => $type,
                'module' => '',
                'source' => $source,
            );
        }
        return $result;
    }

    /**
     * Check models calls
     *
     * Ex.: \Mage::getModel('{Class_Name}')
     *      \Mage::getSingleton('{Class_Name}')
     *      \Mage::getBlockSingleton('{Class_Name}')
     *
     * @param $currentModule
     * @param $fileType
     * @param $file
     * @param $contents
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _caseModelSingleton($currentModule, $fileType, $file, &$contents)
    {
        $patterns = array(
            \Magento\TestFramework\Dependency\RuleInterface::TYPE_HARD =>
            '/(?<source>Mage::(?:getModel|getSingleton|getBlockSingleton)+\([\'"]'
                . '(?<namespace>' . $this->_namespaces . ')[_\\\\]'
                . '(?<module>[A-Z][a-zA-Z]+)\w*[\'"]\))/',
        );
        return $this->_checkDependenciesByRegexp($currentModule, $contents, $patterns);
    }

    /**
     * Check helpers calls
     *
     * Ex.: \Mage::helper('{Class_Name}')
     *      $this->helper('{Class_Name}')
     *
     * @param $currentModule
     * @param $fileType
     * @param $file
     * @param $contents
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _caseHelper($currentModule, $fileType, $file, &$contents)
    {
        $patterns = array(
            \Magento\TestFramework\Dependency\RuleInterface::TYPE_HARD =>
            '/(?<source>[$a-zA-Z0-9_\->:]+helper\([\'"](?<namespace>' . $this->_namespaces . ')[_\\\\]'
                . '(?<module>[A-Z][a-zA-Z]+)\w*)/',
        );
        return $this->_checkDependenciesByRegexp($currentModule, $contents, $patterns);
    }

    /**
     * Check createBlock() methods
     *
     * Ex.: createBlock('{Class_Name}')
     *
     * @param $currentModule
     * @param $fileType
     * @param $file
     * @param $contents
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _caseCreateBlock($currentModule, $fileType, $file, &$contents)
    {
        $patterns = array(
            \Magento\TestFramework\Dependency\RuleInterface::TYPE_HARD =>
            '/[\->:]+(?<source>createBlock\([\'"](?<namespace>' . $this->_namespaces . ')[_\\\\]'
                . '(?<module>[A-Z][a-zA-Z]+)\w*)/',
        );
        return $this->_checkDependenciesByRegexp($currentModule, $contents, $patterns);
    }

    /**
     * Check using of constants
     *
     * Ex.: {Class_Name}::{Constant_Name}
     *
     * @param $currentModule
     * @param $fileType
     * @param $file
     * @param $contents
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _caseConstant($currentModule, $fileType, $file, &$contents)
    {
        $patterns = array(
            \Magento\TestFramework\Dependency\RuleInterface::TYPE_HARD =>
            '/(?<source>(?<namespace>' . $this->_namespaces . ')[_\\\\](?<module>[A-Z][a-zA-Z]+)[_\\\\]'
                . '(?:[A-Z][a-z]+[_\\\\]?){1,}::[A-Z[_\\\\]]+)/',
        );
        return $this->_checkDependenciesByRegexp($currentModule, $contents, $patterns);
    }

    /**
     * Check adding additional files
     *
     * Ex.: $this->getViewFileUrl('{Module_name}::{File_Name}')
     *
     * @param $currentModule
     * @param $fileType
     * @param $file
     * @param $contents
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _caseAddFile($currentModule, $fileType, $file, &$contents)
    {
        $patterns = array(
            \Magento\TestFramework\Dependency\RuleInterface::TYPE_SOFT =>
            '/(?<source>[$a-zA-Z0-9_\->:]+getViewFileUrl\([\'"](?<namespace>' . $this->_namespaces . ')[_\\\\]'
                . '(?<module>[A-Z][a-zA-Z]+)::[\w\/\.-]+)/',
        );
        return $this->_checkDependenciesByRegexp($currentModule, $contents, $patterns);
    }

    /**
     * Check get URL method
     *
     * Ex.: getUrl('{path}')
     *
     * @param $currentModule
     * @param $fileType
     * @param $file
     * @param $contents
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _caseGetUrl($currentModule, $fileType, $file, &$contents)
    {
        $pattern = '/[\->:]+(?<source>getUrl\([\'"](?<router>[\w\/*]+)[\'"])/';

        $dependencies = array();
        if (preg_match_all($pattern, $contents, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $item) {
                $router = str_replace('/', '\\', $item['router']);
                if (isset($this->_mapRouters[$router])) {
                    $moduleName = $this->_mapRouters[$router];
                    if ($currentModule != $moduleName) {
                        $dependencies[] = array(
                            'module' => $moduleName,
                            'type' => \Magento\TestFramework\Dependency\RuleInterface::TYPE_SOFT,
                            'source' => $item['source'],
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
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _caseLayoutBlock($currentModule, $fileType, $file, &$contents)
    {
        $pattern = '/[\->:]+(?<source>(?:getBlock|getBlockHtml)\([\'"](?<block>[\w\.\-]+)[\'"]\))/';

        $area = $this->_getAreaByFile($file);

        $result = array();
        if (preg_match_all($pattern, $contents, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $check = $this->_checkDependencyLayoutBlock($currentModule, $area, $match['block']);
                $module = isset($check['module']) ? $check['module'] : null;
                if ($module) {
                    $result[$module] = array(
                        'type' => \Magento\TestFramework\Dependency\RuleInterface::TYPE_HARD,
                        'source' => $match['source'],
                    );
                }
            }
        }
        return $this->_getUniqueDependencies($result);
    }

    /**
     * Search dependencies by defined regexp patterns
     *
     * @param $currentModule
     * @param $contents
     * @param array $patterns
     * @return array
     */
    protected function _checkDependenciesByRegexp($currentModule, $contents, $patterns = array())
    {
        $result = array();
        foreach ($patterns as $type => $pattern) {
            if (preg_match_all($pattern, $contents, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $match) {
                    $module = $match['namespace'] . '\\' . $match['module'];
                    if ($currentModule != $module) {
                        $result[$module] = array(
                            'type' => $type,
                            'source' => $match['source'],
                        );
                    }
                }
            }
        }
        return $this->_getUniqueDependencies($result);
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
        if (isset($this->_mapLayoutBlocks[$area][$block])) {
            // CASE 1: No dependencies
            $modules = $this->_mapLayoutBlocks[$area][$block];
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

            // CASE 4: \Exception - Undefined dependency
            $undefinedDependency = implode(', ', $modules);
            $this->_exceptions[self::EXCEPTION_TYPE_UNDEFINED_DEPENDENCY][$undefinedDependency] = $undefinedDependency;
        }

        // CASE 5: \Exception - Undefined block
        $this->_exceptions[self::EXCEPTION_TYPE_UNKNOWN_BLOCK][$block] = $block;
        return array();
    }

    /**
     * Get area from file path
     *
     * @param $file
     * @return string
     */
    protected function _getAreaByFile($file)
    {
        $area = 'default';
        if (preg_match('/\/(?<area>adminhtml|frontend)\//', $file, $matches)) {
            $area = $matches['area'];
        }
        return $area;
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
            $result[] = array(
                'module' => $module,
                'type'   => $value['type'],
                'source' => $value['source'],
            );
        }
        return $result;
    }
}
