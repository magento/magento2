<?php
/**
 * Rule for searching dependencies in layout files
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\Dependency;

class LayoutRule implements \Magento\TestFramework\Dependency\RuleInterface
{
    /**
     * Default modules list.
     *
     * @var array
     */
    protected $_defaultModules = [
        'frontend' => 'Magento\Theme',
        'adminhtml' => 'Magento\Adminhtml',
    ];

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
    protected $_mapRouters = [];

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
    protected $_mapLayoutBlocks = [];

    /**
     * List of layout handles
     *
     * Format: array(
     *  '{Area}' => array(
     *   '{Handle_Name}' => array('{Module_Name}' => '{Module_Name}')
     * ))
     *
     * @var array
     */
    protected $_mapLayoutHandles = [];

    /**
     * Unknown layout handle
     */
    const EXCEPTION_TYPE_UNKNOWN_HANDLE = 'UNKNOWN_HANDLE';

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
     *
     * @param array $mapRouters
     * @param array $mapLayoutBlocks
     * @param array $mapLayoutHandles
     */
    public function __construct(array $mapRouters, array $mapLayoutBlocks, array $mapLayoutHandles)
    {
        $this->_mapRouters = $mapRouters;
        $this->_mapLayoutBlocks = $mapLayoutBlocks;
        $this->_mapLayoutHandles = $mapLayoutHandles;
        $this->_namespaces = implode('|', \Magento\Framework\App\Utility\Files::init()->getNamespaces());
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
        if ('layout' != $fileType) {
            return [];
        }

        $attributes = $this->_caseAttributeModule($currentModule, $contents);
        $blocks = $this->_caseElementBlock($currentModule, $contents);
        $actions = $this->_caseElementAction($currentModule, $contents);
        $handle = $this->_caseLayoutHandle($currentModule, $file, $contents);
        $handleParents = $this->_caseLayoutHandleParent($currentModule, $file, $contents);
        $handleUpdates = $this->_caseLayoutHandleUpdate($currentModule, $file, $contents);
        $references = $this->_caseLayoutReference($currentModule, $file, $contents);

        $dependencies = array_merge(
            $attributes,
            $blocks,
            $actions,
            $handle,
            $handleParents,
            $handleUpdates,
            $references
        );
        return $dependencies;
    }

    /**
     * Check dependencies for 'module' attribute
     *
     * Ex.: <element module="{module}">
     *
     * @param $currentModule
     * @param $contents
     * @return array
     */
    protected function _caseAttributeModule($currentModule, &$contents)
    {
        $patterns = [
            '/(?<source><.+module\s*=\s*[\'"](?<namespace>' .
            $this->_namespaces .
            ')[_\\\\]' .
            '(?<module>[A-Z][a-zA-Z]+)[\'"].*>)/' => \Magento\Test\Integrity\DependencyTest::TYPE_SOFT,
        ];
        return $this->_checkDependenciesByRegexp($currentModule, $contents, $patterns);
    }

    /**
     * Check dependencies for <block> element
     *
     * Ex.: <block class="{name}">
     *      <block template="{path}">
     *
     * @param $currentModule
     * @param $contents
     * @return array
     */
    protected function _caseElementBlock($currentModule, &$contents)
    {
        $patterns = [
            '/(?<source><block.*class\s*=\s*[\'"](?<namespace>' .
            $this->_namespaces .
            ')[_\\\\]' .
            '(?<module>[A-Z][a-zA-Z]+)[_\\\\]' .
            '(?:[A-Z][a-zA-Z]+[_\\\\]?){1,}[\'"].*>)/' => \Magento\Test\Integrity\DependencyTest::TYPE_HARD,
            '/(?<source><block.*template\s*=\s*[\'"](?<namespace>' .
            $this->_namespaces .
            ')[_\\\\]' .
            '(?<module>[A-Z][a-zA-Z]+)::[\w\/\.]+[\'"].*>)/' => \Magento\Test\Integrity\DependencyTest::TYPE_SOFT,
        ];
        return $this->_checkDependenciesByRegexp($currentModule, $contents, $patterns);
    }

    /**
     * Check dependencies for <action> element
     *
     * Ex.: <block>{name}
     *      <template>{path}
     *      <file>{path}
     *      <element helper="{name}">
     *
     * @param $currentModule
     * @param $contents
     * @return array
     */
    protected function _caseElementAction($currentModule, &$contents)
    {
        $patterns = [
            '/(?<source><block\s*>(?<namespace>' .
            $this->_namespaces .
            ')[_\\\\]' .
            '(?<module>[A-Z][a-zA-Z]+)[_\\\\]' .
            '(?:[A-Z][a-zA-Z]+[_\\\\]?){1,}<\/block\s*>)/' => \Magento\Test\Integrity\DependencyTest::TYPE_SOFT,
            '/(?<source><template\s*>(?<namespace>' .
            $this->_namespaces .
            ')[_\\\\]' .
            '(?<module>[A-Z][a-zA-Z]+)::[\w\/\.]+' .
            '<\/template\s*>)/' => \Magento\Test\Integrity\DependencyTest::TYPE_SOFT,
            '/(?<source><file\s*>(?<namespace>' .
            $this->_namespaces .
            ')[_\\\\]' .
            '(?<module>[A-Z][a-zA-Z]+)::[\w\/\.-]+' .
            '<\/file\s*>)/' => \Magento\Test\Integrity\DependencyTest::TYPE_SOFT,
            '/(?<source><.*helper\s*=\s*[\'"](?<namespace>' .
            $this->_namespaces .
            ')[_\\\\]' .
            '(?<module>[A-Z][a-zA-Z]+)[_\\\\](?:[A-Z][a-z]+[_\\\\]?){1,}::[\w]+' .
            '[\'"].*>)/' => \Magento\Test\Integrity\DependencyTest::TYPE_SOFT,
        ];
        return $this->_checkDependenciesByRegexp($currentModule, $contents, $patterns);
    }

    /**
     * Check layout handles
     *
     * Ex.: <layout><{name}>...</layout>
     *
     * @param $currentModule
     * @param $file
     * @param $contents
     * @return array
     */
    protected function _caseLayoutHandle($currentModule, $file, &$contents)
    {
        $xml = simplexml_load_string($contents);
        if (!$xml) {
            return [];
        }

        $area = $this->_getAreaByFile($file);

        $result = [];
        foreach ((array)$xml->xpath('/layout/child::*') as $element) {
            $check = $this->_checkDependencyLayoutHandle($currentModule, $area, $element->getName());
            $modules = isset($check['module']) ? $check['module'] : null;
            if ($modules) {
                if (!is_array($modules)) {
                    $modules = [$modules];
                }
                foreach ($modules as $module) {
                    $result[$module] = [
                        'type' => \Magento\Test\Integrity\DependencyTest::TYPE_SOFT,
                        'source' => $element->getName(),
                    ];
                }
            }
        }
        return $this->_getUniqueDependencies($result);
    }

    /**
     * Check layout handles parents
     *
     * Ex.: <layout_name  parent="{name}">
     *
     * @param $currentModule
     * @param $file
     * @param $contents
     * @return array
     */
    protected function _caseLayoutHandleParent($currentModule, $file, &$contents)
    {
        $xml = simplexml_load_string($contents);
        if (!$xml) {
            return [];
        }

        $area = $this->_getAreaByFile($file);

        $result = [];
        foreach ((array)$xml->xpath('/layout/child::*/@parent') as $element) {
            $check = $this->_checkDependencyLayoutHandle($currentModule, $area, (string)$element);
            $modules = isset($check['module']) ? $check['module'] : null;
            if ($modules) {
                if (!is_array($modules)) {
                    $modules = [$modules];
                }
                foreach ($modules as $module) {
                    $result[$module] = [
                        'type' => \Magento\Test\Integrity\DependencyTest::TYPE_HARD,
                        'source' => (string)$element,
                    ];
                }
            }
        }
        return $this->_getUniqueDependencies($result);
    }

    /**
     * Check layout handles updates
     *
     * Ex.: <update handle="{name}" />
     *
     * @param $currentModule
     * @param $file
     * @param $contents
     * @return array
     */
    protected function _caseLayoutHandleUpdate($currentModule, $file, &$contents)
    {
        $xml = simplexml_load_string($contents);
        if (!$xml) {
            return [];
        }

        $area = $this->_getAreaByFile($file);

        $result = [];
        foreach ((array)$xml->xpath('//update/@handle') as $element) {
            $check = $this->_checkDependencyLayoutHandle($currentModule, $area, (string)$element);
            $modules = isset($check['module']) ? $check['module'] : null;
            if ($modules) {
                if (!is_array($modules)) {
                    $modules = [$modules];
                }
                foreach ($modules as $module) {
                    $result[$module] = [
                        'type' => \Magento\Test\Integrity\DependencyTest::TYPE_SOFT,
                        'source' => (string)$element,
                    ];
                }
            }
        }
        return $this->_getUniqueDependencies($result);
    }

    /**
     * Check layout references
     *
     * Ex.: <reference name="{name}">
     *
     * @param $currentModule
     * @param $file
     * @param $contents
     * @return array
     */
    protected function _caseLayoutReference($currentModule, $file, &$contents)
    {
        $xml = simplexml_load_string($contents);
        if (!$xml) {
            return [];
        }

        $area = $this->_getAreaByFile($file);

        $result = [];
        foreach ((array)$xml->xpath('//reference/@name') as $element) {
            $check = $this->_checkDependencyLayoutBlock($currentModule, $area, (string)$element);
            $module = isset($check['module']) ? $check['module'] : null;
            if ($module) {
                $result[$module] = [
                    'type' => \Magento\TestFramework\Dependency\RuleInterface::TYPE_SOFT,
                    'source' => (string)$element,
                ];
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
    protected function _checkDependenciesByRegexp($currentModule, &$contents, $patterns = [])
    {
        $result = [];

        foreach ($patterns as $pattern => $type) {
            if (preg_match_all($pattern, $contents, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $match) {
                    $module = $match['namespace'] . '\\' . $match['module'];

                    if ($currentModule != $module) {
                        $result[$module] = ['type' => $type, 'source' => $match['source']];
                    }
                }
            }
        }
        return $this->_getUniqueDependencies($result);
    }

    /**
     * Check layout handle dependency
     *
     * Return: array(
     *  'module'  // dependent module
     *  'source'  // source text
     * )
     *
     * @param $currentModule
     * @param $area
     * @param $handle
     * @return array
     */
    protected function _checkDependencyLayoutHandle($currentModule, $area, $handle)
    {
        $chunks = explode('_', $handle);
        if (count($chunks) > 1) {
            // Remove 'action' part from handle name
            array_pop($chunks);
        }
        $router = implode('_', $chunks);
        if (isset($this->_mapRouters[$router])) {
            // CASE 1: Single dependency
            $modules = $this->_mapRouters[$router];
            if (!in_array($currentModule, $modules)) {
                return ['module' => $modules];
            }
        }

        if (isset($this->_mapLayoutHandles[$area][$handle])) {
            // CASE 2: No dependencies
            $modules = $this->_mapLayoutHandles[$area][$handle];
            if (isset($modules[$currentModule])) {
                return ['module' => null];
            }

            // CASE 3: Single dependency
            if (1 == count($modules)) {
                return ['module' => current($modules)];
            }

            // CASE 4: Default module dependency
            $defaultModule = $this->_getDefaultModuleName($area);
            if (isset($modules[$defaultModule])) {
                return ['module' => $defaultModule];
            }
        }

        return [];
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
                return ['module' => null];
            }

            // CASE 2: Single dependency
            if (1 == count($modules)) {
                return ['module' => current($modules)];
            }

            // CASE 3: Default module dependency
            $defaultModule = $this->_getDefaultModuleName($area);
            if (isset($modules[$defaultModule])) {
                return ['module' => $defaultModule];
            }
        }
        return [];
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
     * Retrieve unique dependencies
     *
     * @param array $dependencies
     * @return array
     */
    protected function _getUniqueDependencies($dependencies = [])
    {
        $result = [];
        foreach ($dependencies as $module => $value) {
            $result[] = ['module' => $module, 'type' => $value['type'], 'source' => $value['source']];
        }
        return $result;
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
}
