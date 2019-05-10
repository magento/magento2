<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\CompiledInterception\Generator;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Config\ReaderInterface;
use Magento\Framework\Interception\DefinitionInterface;
use Magento\Framework\Interception\PluginListInterface as InterceptionPluginList;
use Magento\Framework\Interception\ObjectManager\ConfigInterface;
use Magento\Framework\ObjectManager\Config\Reader\Dom\Proxy;
use Magento\Framework\ObjectManager\RelationsInterface;
use Magento\Framework\ObjectManager\DefinitionInterface as ClassDefinitions;

/**
 * Plugin config, provides list of plugins for a type
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CompiledPluginList implements InterceptionPluginList
{
    /**
     * Configuration reader
     *
     * @var ReaderInterface
     */
    protected $_reader;

    /**
     * Config data
     *
     * @var array
     */
    protected $_data = [];

    //SCOPED

    /**
     * Scope priority loading scheme
     *
     * @var string[]
     */
    protected $_scopePriorityScheme = [];

    /**
     * Loaded scopes
     *
     * @var array
     */
    protected $_loadedScopes = [];

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
     * @var array
     */
    protected $_pluginInstances = [];

    /**
     * @var array
     */
    protected $currentScope;

    /**
     * @var array
     */
    protected $_cachePath;

    public function __construct(
        $scope,
        $reader = null,
        $omConfig = null,
        $cachePath = null
    ) {
        $this->currentScope = $scope;

        if ($reader && $omConfig) {
            $this->_reader = $reader;
            $this->_omConfig = $omConfig;
        } else {
            $objectManager = ObjectManager::getInstance();
            $this->_reader = $objectManager->get(Proxy::class);
            $this->_omConfig = $objectManager->get(ConfigInterface::class);
        }
        $this->_relations = new \Magento\Framework\ObjectManager\Relations\Runtime();
        $this->_definitions = new \Magento\Framework\Interception\Definition\Runtime();
        $this->_classDefinitions = new \Magento\Framework\ObjectManager\Definition\Runtime();
        $this->_scopePriorityScheme = ['first' => 'global'];
        $this->_cachePath = ($cachePath === null ? BP . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'cache' : $cachePath);
    }

    /**
     * Collect parent types configuration for requested type
     *
     * @param string $type
     * @return array
     * @throws \InvalidArgumentException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _inheritPlugins($type)
    {
        $type = ltrim($type, '\\');
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
                $this->filterPlugins($plugins);
                uasort($plugins, [$this, '_sort']);
                $this->trimInstanceStartingBackslash($plugins);
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
                        throw new \InvalidArgumentException('Plugin class ' . $pluginType . ' doesn\'t exist');
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
     * Trims starting backslash from plugin instance name
     *
     * @param array $plugins
     * @return void
     */
    private function trimInstanceStartingBackslash(&$plugins)
    {
        foreach ($plugins as &$plugin) {
            $plugin['instance'] = ltrim($plugin['instance'], '\\');
        }
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
            return (0 - (int)$itemB['sortOrder']);
        } else {
            return 0;
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
        return null;
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

    protected function _loadScopedData()
    {
        if (false == isset($this->_loadedScopes[$this->currentScope])) {
            if (false == in_array($this->currentScope, $this->_scopePriorityScheme)) {
                $this->_scopePriorityScheme[] = $this->currentScope;
            }
            if ($this->_cachePath) {
                $cacheId = $this->_cachePath . DIRECTORY_SEPARATOR . 'compiled-plugins_' . implode('_', $this->_scopePriorityScheme) . '.php';
                $data = @include($cacheId);
            } else {
                $data = null;
            }

            if ($data) {
                list($this->_data, $this->_inherited, $this->_processed) = $data;
                foreach ($this->_scopePriorityScheme as $scopeCode) {
                    $this->_loadedScopes[$scopeCode] = true;
                }
            } else {
                $virtualTypes = [];
                foreach ($this->_scopePriorityScheme as $scopeCode) {
                    if (false == isset($this->_loadedScopes[$scopeCode])) {
                        $data = $this->_reader->read($scopeCode);
                        unset($data['preferences']);
                        if (count($data) > 0) {
                            $this->_inherited = [];
                            $this->_processed = [];
                            $this->merge($data);
                            foreach ($data as $class => $config) {
                                if (isset($config['type'])) {
                                    $virtualTypes[] = $class;
                                }
                            }
                        }
                        $this->_loadedScopes[$scopeCode] = true;
                    }
                    if ($scopeCode == $this->currentScope) {
                        break;
                    }
                }
                foreach ($virtualTypes as $class) {
                    $this->_inheritPlugins($class);
                }
                foreach ($this->getClassDefinitions() as $class) {
                    $this->_inheritPlugins($class);
                }
                if ($this->_cachePath) {
                    file_put_contents(
                        $cacheId,
                        '<?php return ' . var_export([$this->_data, $this->_inherited, $this->_processed], true) . '?>'
                    );
                }
            }
            $this->_pluginInstances = [];
        }
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

    public function getPluginType($type, $code)
    {
        return $this->_inherited[$type][$code]['instance'];
    }

    /**
     * Remove from list not existing plugins
     *
     * @param array $plugins
     * @return void
     */
    private function filterPlugins(array &$plugins)
    {
        foreach ($plugins as $name => $plugin) {
            if (empty($plugin['instance'])) {
                unset($plugins[$name]);
                //$this->getLogger()->info("Reference to undeclared plugin with name '{$name}'.");
            }
        }
    }
}
