<?php
/**
 * Plugin configuration storage. Provides list of plugins configured for type.
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
namespace Magento\Interception\PluginList;

use Magento\Config\ReaderInterface;
use Magento\Config\ScopeInterface;
use Magento\Config\CacheInterface;
use Magento\Config\Data\Scoped;
use Magento\Interception\Definition;
use Magento\Interception\PluginList as InterceptionPluginList;
use Magento\ObjectManager\Config;
use Magento\ObjectManager\Relations;
use Magento\ObjectManager\Definition\Compiled;
use Zend\Soap\Exception\InvalidArgumentException;

class PluginList extends Scoped implements InterceptionPluginList
{
    /**
     * Type config
     *
     * @var Config
     */
    protected $_omConfig;

    /**
     * Class relations information provider
     *
     * @var Relations
     */
    protected $_relations;

    /**
     * List of interception methods per plugin
     *
     * @var Definition
     */
    protected $_definitions;

    /**
     * List of interceptable application classes
     *
     * @var Compiled
     */
    protected $_classDefinitions;

    /**
     * Scope inheritance scheme
     *
     * @var string[]
     */
    protected $_scopePriorityScheme = array('global');

    /**
     * @param ReaderInterface $reader
     * @param ScopeInterface $configScope
     * @param CacheInterface $cache
     * @param Relations $relations
     * @param Config $omConfig
     * @param Definition $definitions
     * @param string[] $scopePriorityScheme
     * @param string $cacheId
     * @param Compiled $classDefinitions
     */
    public function __construct(
        ReaderInterface $reader,
        ScopeInterface $configScope,
        CacheInterface $cache,
        Relations $relations,
        Config $omConfig,
        Definition $definitions,
        array $scopePriorityScheme,
        $cacheId = 'plugins',
        Compiled $classDefinitions = null
    ) {
        parent::__construct($reader, $configScope, $cache, $cacheId);
        $this->_omConfig = $omConfig;
        $this->_relations = $relations;
        $this->_definitions = $definitions;
        $this->_classDefinitions = $classDefinitions;
        $this->_scopePriorityScheme = $scopePriorityScheme;
    }

    /**
     * Collect parent types configuration for requested type
     *
     * @param string $type
     * @return array
     * @throws InvalidArgumentException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _inheritPlugins($type)
    {
        if (!isset($this->_data['inherited'][$type])) {
            $realType = $this->_omConfig->getInstanceType($type);

            if ($realType !== $type) {
                $plugins = $this->_inheritPlugins($realType);
            } else if ($this->_relations->has($type)) {
                $relations = $this->_relations->getParents($type);
                $plugins = array();
                foreach ($relations as $relation) {
                    if ($relation) {
                        $relationPlugins = $this->_inheritPlugins($relation);
                        if ($relationPlugins) {
                            $plugins = array_replace_recursive($plugins, $relationPlugins);
                        }
                    }
                }
            } else {
                $plugins = array();
            }
            if (isset($this->_data[$type]['plugins'])) {
                if (!$plugins) {
                    $plugins = $this->_data[$type]['plugins'];
                } else {
                    $plugins = array_replace_recursive($plugins, $this->_data[$type]['plugins']);
                }
            }
            uasort($plugins, array($this, '_sort'));
            if (count($plugins)) {
                $this->_data['inherited'][$type] = $plugins;
                foreach ($plugins as $key => $plugin) {
                    // skip disabled plugins
                    if (isset($plugin['disabled']) && $plugin['disabled']) {
                        unset($plugins[$key]);
                        continue;
                    }
                    $pluginType = $this->_omConfig->getInstanceType($plugin['instance']);
                    if (!class_exists($pluginType)) {
                        throw new InvalidArgumentException('Plugin class ' . $pluginType . ' doesn\'t exist');
                    }
                    foreach ($this->_definitions->getMethodList($pluginType) as $pluginMethod) {
                        $this->_data['processed'][$type][$pluginMethod][] = $plugin['instance'];
                    }
                }
            } else {
                $this->_data['inherited'][$type] = null;
            }
            return $plugins;
        }
        return $this->_data['inherited'][$type];
    }

    /**
     * Sort items
     *
     * @param array $itemA
     * @param array $itemB
     * @return int
     */
    protected function _sort($itemA, $itemB)
    {
        if (isset($itemA['sortOrder'])) {
            if (isset($itemB['sortOrder'])) {
                return $itemA['sortOrder'] - $itemB['sortOrder'];
            }
            return $itemA['sortOrder'];
        } else if (isset($itemB['sortOrder'])) {
            return $itemB['sortOrder'];
        } else {
            return 1;
        }
    }

    /**
     * {@inheritdoc}
     *
     * @param string $type
     * @param string $method
     * @param string $scenario
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getPlugins($type, $method, $scenario)
    {
        $this->_loadScopedData();
        $pluginMethodName = $scenario . ucfirst($method);
        $realType = $this->_omConfig->getInstanceType($type);
        if (!isset($this->_data['inherited'][$realType])) {
            $this->_inheritPlugins($type);
        }
        return isset($this->_data['processed'][$realType][$pluginMethodName])
            ? $this->_data['processed'][$realType][$pluginMethodName]
            : array();
    }

    /**
     * Load configuration for current scope
     *
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _loadScopedData()
    {
        $scope = $this->_configScope->getCurrentScope();
        if (false == isset($this->_loadedScopes[$scope])) {
            if (false == in_array($scope, $this->_scopePriorityScheme)) {
                $this->_scopePriorityScheme[] = $scope;
            }
            $cacheId = implode('|', $this->_scopePriorityScheme) . "|" . $this->_cacheId;
            $data = $this->_cache->load($cacheId);
            if ($data) {
                $this->_data = unserialize($data);
                foreach ($this->_scopePriorityScheme as $scope) {
                    $this->_loadedScopes[$scope] = true;
                }
            } else {
                foreach ($this->_scopePriorityScheme as $scopeCode) {
                    if (false == isset($this->_loadedScopes[$scopeCode])) {
                        $data = $this->_reader->read($scopeCode);
                        if (!count($data)) {
                            continue;
                        }
                        unset($this->_data['inherited']);
                        unset($this->_data['processed']);
                        $this->merge($data);
                        $this->_loadedScopes[$scopeCode] = true;
                    }
                    if ($scopeCode == $scope) {
                        break;
                    }
                }
                if ($this->_classDefinitions) {
                    foreach ($this->_classDefinitions->getClasses() as $class) {
                        $this->_inheritPlugins($class);
                    }
                }
                $this->_cache->save(serialize($this->_data), $cacheId);
            }
        }
    }
}
