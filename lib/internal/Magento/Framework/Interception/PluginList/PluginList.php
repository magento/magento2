<?php
/**
 * Plugin configuration storage. Provides list of plugins configured for type.
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Interception\PluginList;

use Magento\Framework\Config\CacheInterface;
use Magento\Framework\Config\Data\Scoped;
use Magento\Framework\Config\ReaderInterface;
use Magento\Framework\Config\ScopeInterface;
use Magento\Framework\Interception\DefinitionInterface;
use Magento\Framework\Interception\PluginListInterface as InterceptionPluginList;
use Magento\Framework\Interception\ObjectManager\ConfigInterface;
use Magento\Framework\ObjectManager\RelationsInterface;
use Magento\Framework\ObjectManager\DefinitionInterface as ClassDefinitions;
use Magento\Framework\ObjectManagerInterface;
use Zend\Soap\Exception\InvalidArgumentException;

class PluginList extends Scoped implements InterceptionPluginList
{
    /**
     * Inherited plugin data
     *
     * @var array
     */
    protected $_inherited;

    /**
     * Inherited plugin data, preprocessed for read
     *
     * @var array
     */
    protected $_processed;

    /**
     * Type config
     *
     * @var ConfigInterface
     */
    protected $_omConfig;

    /**
     * Class relations information provider
     *
     * @var RelationsInterface
     */
    protected $_relations;

    /**
     * List of interception methods per plugin
     *
     * @var DefinitionInterface
     */
    protected $_definitions;

    /**
     * List of interceptable application classes
     *
     * @var ClassDefinitions
     */
    protected $_classDefinitions;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var array
     */
    protected $_pluginInstances = [];

    /**
     * @param ReaderInterface $reader
     * @param ScopeInterface $configScope
     * @param CacheInterface $cache
     * @param RelationsInterface $relations
     * @param ConfigInterface $omConfig
     * @param DefinitionInterface $definitions
     * @param ObjectManagerInterface $objectManager
     * @param ClassDefinitions $classDefinitions
     * @param array $scopePriorityScheme
     * @param string $cacheId
     */
    public function __construct(
        ReaderInterface $reader,
        ScopeInterface $configScope,
        CacheInterface $cache,
        RelationsInterface $relations,
        ConfigInterface $omConfig,
        DefinitionInterface $definitions,
        ObjectManagerInterface $objectManager,
        ClassDefinitions $classDefinitions,
        array $scopePriorityScheme = ['global'],
        $cacheId = 'plugins'
    ) {
        parent::__construct($reader, $configScope, $cache, $cacheId);
        $this->_omConfig = $omConfig;
        $this->_relations = $relations;
        $this->_definitions = $definitions;
        $this->_classDefinitions = $classDefinitions;
        $this->_scopePriorityScheme = $scopePriorityScheme;
        $this->_objectManager = $objectManager;
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
        if (!array_key_exists($type, $this->_inherited)) {
            $realType = $this->_omConfig->getOriginalInstanceType($type);

            if ($realType !== $type) {
                $plugins = $this->_inheritPlugins($realType);
            } elseif ($this->_relations->has($type)) {
                $relations = $this->_relations->getParents($type);
                $plugins = [];
                foreach ($relations as $relation) {
                    if ($relation) {
                        $relationPlugins = $this->_inheritPlugins($relation);
                        if ($relationPlugins) {
                            $plugins = array_replace_recursive($plugins, $relationPlugins);
                        }
                    }
                }
            } else {
                $plugins = [];
            }
            if (isset($this->_data[$type])) {
                if (!$plugins) {
                    $plugins = $this->_data[$type];
                } else {
                    $plugins = array_replace_recursive($plugins, $this->_data[$type]);
                }
            }
            $this->_inherited[$type] = null;
            if (is_array($plugins) && count($plugins)) {
                uasort($plugins, [$this, '_sort']);
                $this->_inherited[$type] = $plugins;
                $lastPerMethod = [];
                foreach ($plugins as $key => $plugin) {
                    // skip disabled plugins
                    if (isset($plugin['disabled']) && $plugin['disabled']) {
                        unset($plugins[$key]);
                        continue;
                    }
                    $pluginType = $this->_omConfig->getOriginalInstanceType($plugin['instance']);
                    if (!class_exists($pluginType)) {
                        throw new InvalidArgumentException('Plugin class ' . $pluginType . ' doesn\'t exist');
                    }
                    foreach ($this->_definitions->getMethodList($pluginType) as $pluginMethod => $methodTypes) {
                        $current = isset($lastPerMethod[$pluginMethod]) ? $lastPerMethod[$pluginMethod] : '__self';
                        $currentKey = $type . '_' . $pluginMethod . '_' . $current;
                        if ($methodTypes & DefinitionInterface::LISTENER_AROUND) {
                            $this->_processed[$currentKey][DefinitionInterface::LISTENER_AROUND] = $key;
                            $lastPerMethod[$pluginMethod] = $key;
                        }
                        if ($methodTypes & DefinitionInterface::LISTENER_BEFORE) {
                            $this->_processed[$currentKey][DefinitionInterface::LISTENER_BEFORE][] = $key;
                        }
                        if ($methodTypes & DefinitionInterface::LISTENER_AFTER) {
                            $this->_processed[$currentKey][DefinitionInterface::LISTENER_AFTER][] = $key;
                        }
                    }
                }
            }
            return $plugins;
        }
        return $this->_inherited[$type];
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
        } elseif (isset($itemB['sortOrder'])) {
            return $itemB['sortOrder'];
        } else {
            return 1;
        }
    }

    /**
     * Retrieve plugin Instance
     *
     * @param string $type
     * @param string $code
     * @return mixed
     */
    public function getPlugin($type, $code)
    {
        if (!isset($this->_pluginInstances[$type][$code])) {
            $this->_pluginInstances[$type][$code] = $this->_objectManager->get(
                $this->_inherited[$type][$code]['instance']
            );
        }
        return $this->_pluginInstances[$type][$code];
    }

    /**
     * Retrieve next plugins in chain
     *
     * @param string $type
     * @param string $method
     * @param string $code
     * @return array
     */
    public function getNext($type, $method, $code = '__self')
    {
        $this->_loadScopedData();
        if (!isset($this->_inherited[$type]) && !array_key_exists($type, $this->_inherited)) {
            $this->_inheritPlugins($type);
        }
        $key = $type . '_' . lcfirst($method) . '_' . $code;
        return isset($this->_processed[$key]) ? $this->_processed[$key] : null;
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
                list($this->_data, $this->_inherited, $this->_processed) = unserialize($data);
                foreach ($this->_scopePriorityScheme as $scope) {
                    $this->_loadedScopes[$scope] = true;
                }
            } else {
                $virtualTypes = [];
                foreach ($this->_scopePriorityScheme as $scopeCode) {
                    if (false == isset($this->_loadedScopes[$scopeCode])) {
                        $data = $this->_reader->read($scopeCode);
                        unset($data['preferences']);
                        if (!count($data)) {
                            continue;
                        }
                        $this->_inherited = [];
                        $this->_processed = [];
                        $this->merge($data);
                        $this->_loadedScopes[$scopeCode] = true;
                        foreach ($data as $class => $config) {
                            if (isset($config['type'])) {
                                $virtualTypes[] = $class;
                            }
                        }
                    }
                    if ($this->isCurrentScope($scopeCode)) {
                        break;
                    }
                }
                foreach ($virtualTypes as $class) {
                    $this->_inheritPlugins(ltrim($class, '\\'));
                }
                foreach ($this->getClassDefinitions() as $class) {
                    $this->_inheritPlugins($class);
                }
                $this->_cache->save(serialize([$this->_data, $this->_inherited, $this->_processed]), $cacheId);
            }
            $this->_pluginInstances = [];
        }
    }

    /**
     * Whether scope code is current scope code
     *
     * @param string $scopeCode
     * @return bool
     */
    protected function isCurrentScope($scopeCode)
    {
        return $this->_configScope->getCurrentScope() == $scopeCode;
    }

    /**
     * Returns class definitions
     *
     * @return array
     */
    protected function getClassDefinitions()
    {
        return $this->_classDefinitions->getClasses();
    }

    /**
     * Merge configuration
     *
     * @param array $config
     * @return void
     */
    public function merge(array $config)
    {
        foreach ($config as $type => $typeConfig) {
            if (isset($typeConfig['plugins'])) {
                $type = ltrim($type, '\\');
                if (isset($this->_data[$type])) {
                    $this->_data[$type] = array_replace_recursive($this->_data[$type], $typeConfig['plugins']);
                } else {
                    $this->_data[$type] = $typeConfig['plugins'];
                }
            }
        }
    }
}
