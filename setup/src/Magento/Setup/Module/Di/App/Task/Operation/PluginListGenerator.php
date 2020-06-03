<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\Di\App\Task\Operation;

use Magento\Framework\App\ObjectManager\ConfigWriterInterface;
use Magento\Framework\Config\ReaderInterface;
use Magento\Framework\Config\ScopeInterface;
use Magento\Framework\Interception\DefinitionInterface;
use Magento\Framework\Interception\ObjectManager\ConfigInterface;
use Magento\Framework\ObjectManager\DefinitionInterface as ClassDefinitions;
use Magento\Framework\ObjectManager\RelationsInterface;
use Magento\Setup\Module\Di\App\Task\OperationInterface;
use Magento\Framework\Config\CacheInterface;
use Psr\Log\LoggerInterface;

/**
 * Generates plugins for Magento per scope
 */
class PluginListGenerator implements OperationInterface
{
    /**
     * @var ScopeInterface
     */
    private $scopeConfig;

    /**
     * Configuration reader
     *
     * @var ReaderInterface
     */
    private $reader;

    /**
     * Configuration cache
     *
     * @var CacheInterface
     */
    protected $cache;

    /**
     * Cache tag
     *
     * @var string
     */
    protected $_cacheId = 'plugin-list';

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
     * Type config
     *
     * @var ConfigInterface
     */
    private $omConfig;

    /**
     * Class relations information provider
     *
     * @var RelationsInterface
     */
    private $relations;

    /**
     * List of interception methods per plugin
     *
     * @var DefinitionInterface
     */
    private $definitions;

    /**
     * List of interceptable application classes
     *
     * @var ClassDefinitions
     */
    private $classDefinitions;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ConfigWriterInterface
     */
    private $configWriter;

    /**
     * @var array
     */
    private $_data;

    /**
     * @var array
     */
    private $globalScopePluginData = [];

    /**
     * @var array
     */
    private $_inherited = [];

    /**
     * @var array
     */
    private $_processed;

    /**
     * @var array
     */
    protected $_pluginInstances = [];

    /**
     * @param ReaderInterface $reader
     * @param ScopeInterface $scopeConfig
     * @param ConfigInterface $omConfig
     * @param RelationsInterface $relations
     * @param DefinitionInterface $definitions
     * @param ClassDefinitions $classDefinitions
     * @param LoggerInterface $logger
     * @param CacheInterface $cache
     * @param ConfigWriterInterface $configWriter
     * @param array $scopePriorityScheme
     */
    public function __construct(
        ReaderInterface $reader,
        ScopeInterface $scopeConfig,
        ConfigInterface $omConfig,
        RelationsInterface $relations,
        DefinitionInterface $definitions,
        ClassDefinitions $classDefinitions,
        LoggerInterface $logger,
        CacheInterface $cache,
        ConfigWriterInterface $configWriter,
        array $scopePriorityScheme = ['global']
    ) {
        $this->reader = $reader;
        $this->scopeConfig = $scopeConfig;
        $this->omConfig = $omConfig;
        $this->relations = $relations;
        $this->definitions = $definitions;
        $this->classDefinitions = $classDefinitions;
        $this->logger = $logger;
        $this->cache = $cache;
        $this->_scopePriorityScheme = $scopePriorityScheme;
        $this->configWriter = $configWriter;
    }

    /**
     * @inheritDoc
     */
    public function doOperation()
    {
        $scopes = $this->scopeConfig->getAllScopes();
        array_shift($scopes);

        foreach ($scopes as $scope) {
            $this->scopeConfig->setCurrentScope($scope);
            if (false === isset($this->_loadedScopes[$scope])) {
                if (false === in_array($scope, $this->_scopePriorityScheme)) {
                    $this->_scopePriorityScheme[] = $scope;
                }
                $cacheId = implode('|', $this->_scopePriorityScheme) . "|" . $this->_cacheId;

                foreach ($this->_loadScopedVirtualTypes() as $class) {
                    $this->_inheritPlugins($class);
                }
                foreach ($this->_data as $className => $value) {
                    $this->_inheritPlugins($className);
                }
                foreach ($this->getClassDefinitions() as $class) {
                    $this->_inheritPlugins($class);
                }
                if ($scope === 'global') {
                    $this->globalScopePluginData = $this->_data;
                }
                $this->configWriter->write(
                    $cacheId,
                    [$this->_data, $this->_inherited, $this->_processed]
                );
                if (count($this->_scopePriorityScheme) > 1 ) {
                    array_pop($this->_scopePriorityScheme);
                    // merge global scope plugin data to other scopes by default
                    $this->_data = $this->globalScopePluginData;
                }
                $this->_pluginInstances = [];
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return 'Plugin list generation';
    }

    /**
     * Load virtual types for current scope
     *
     * @return array
     */
    private function _loadScopedVirtualTypes()
    {
        $virtualTypes = [];
        foreach ($this->_scopePriorityScheme as $scopeCode) {
            if (!isset($this->_loadedScopes[$scopeCode])) {
                $data = $this->reader->read($scopeCode) ?: [];
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
            if ($this->isCurrentScope($scopeCode)) {
                break;
            }
        }
        return $virtualTypes;
    }

    /**
     * Returns class definitions
     *
     * @return array
     */
    private function getClassDefinitions()
    {
        return $this->classDefinitions->getClasses();
    }

    /**
     * Whether scope code is current scope code
     *
     * @param string $scopeCode
     * @return bool
     */
    private function isCurrentScope($scopeCode)
    {
        return $this->scopeConfig->getCurrentScope() === $scopeCode;
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
    private function _inheritPlugins($type)
    {
        $type = ltrim($type, '\\');
        if (!isset($this->_inherited[$type])) {
            $realType = $this->omConfig->getOriginalInstanceType($type);

            if ($realType !== $type) {
                $plugins = $this->_inheritPlugins($realType);
            } elseif ($this->relations->has($type)) {
                $relations = $this->relations->getParents($type);
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
                    $pluginType = $this->omConfig->getOriginalInstanceType($plugin['instance']);
                    if (!class_exists($pluginType)) {
                        throw new \InvalidArgumentException('Plugin class ' . $pluginType . ' doesn\'t exist');
                    }
                    foreach ($this->definitions->getMethodList($pluginType) as $pluginMethod => $methodTypes) {
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
                $this->logger->info("Reference to undeclared plugin with name '{$name}'.");
            }
        }
    }

    /**
     * Merge configuration
     *
     * @param array $config
     * @return void
     */
    private function merge(array $config)
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

    /**
     * Sort items
     *
     * @param array $itemA
     * @param array $itemB
     * @return int
     */
    private function _sort($itemA, $itemB)
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

}
