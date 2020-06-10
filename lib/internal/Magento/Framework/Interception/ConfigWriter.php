<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Interception;

use Magento\Framework\App\ObjectManager\ConfigWriterInterface as ObjectManagerConfigWriterInterface;
use Magento\Framework\Config\ReaderInterface;
use Magento\Framework\Config\ScopeInterface;
use Magento\Framework\Interception\ObjectManager\ConfigInterface;
use Magento\Framework\ObjectManager\DefinitionInterface as ClassDefinitions;
use Magento\Framework\ObjectManager\RelationsInterface;
use Psr\Log\LoggerInterface;

/**
 * Interception configuration writer for scopes.
 */
class ConfigWriter implements ConfigWriterInterface
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
     * Cache tag
     *
     * @var string
     */
    private $cacheId = 'plugin-list';

    /**
     * Loaded scopes
     *
     * @var array
     */
    private $loadedScopes = [];

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
     * @var ObjectManagerConfigWriterInterface
     */
    private $configWriter;

    /**
     * @var array
     */
    private $pluginData;

    /**
     * @var array
     */
    private $inherited = [];

    /**
     * @var array
     */
    private $processed;

    /**
     * Scope priority loading scheme
     *
     * @var string[]
     */
    private $scopePriorityScheme;

    /**
     * @var array
     */
    private $globalScopePluginData = [];

    /**
     * @param ReaderInterface $reader
     * @param ScopeInterface $scopeConfig
     * @param ConfigInterface $omConfig
     * @param RelationsInterface $relations
     * @param DefinitionInterface $definitions
     * @param ClassDefinitions $classDefinitions
     * @param LoggerInterface $logger
     * @param ObjectManagerConfigWriterInterface $configWriter
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
        ObjectManagerConfigWriterInterface $configWriter,
        array $scopePriorityScheme = ['global']
    ) {
        $this->reader = $reader;
        $this->scopeConfig = $scopeConfig;
        $this->omConfig = $omConfig;
        $this->relations = $relations;
        $this->definitions = $definitions;
        $this->classDefinitions = $classDefinitions;
        $this->logger = $logger;
        $this->configWriter = $configWriter;
        $this->scopePriorityScheme = $scopePriorityScheme;
    }

    /**
     * @inheritDoc
     */
    public function write($scopes)
    {
        foreach ($scopes as $scope) {
            $this->scopeConfig->setCurrentScope($scope);
            if (false === isset($this->loadedScopes[$scope])) {
                if (false === in_array($scope, $this->scopePriorityScheme, true)) {
                    $this->scopePriorityScheme[] = $scope;
                }
                $cacheId = implode('|', $this->scopePriorityScheme) . "|" . $this->cacheId;
                foreach ($this->loadScopedVirtualTypes() as $class) {
                    $this->inheritPlugins($class);
                }
                foreach ($this->pluginData as $className => $value) {
                    $this->inheritPlugins($className);
                }
                foreach ($this->getClassDefinitions() as $class) {
                    $this->inheritPlugins($class);
                }
                $this->configWriter->write(
                    $cacheId,
                    [$this->pluginData, $this->inherited, $this->processed]
                );
                // need global & primary scopes plugin data for other scopes
                if ($scope === 'global') {
                    $this->globalScopePluginData = $this->pluginData;
                }
                if (count($this->scopePriorityScheme) > 2) {
                    array_pop($this->scopePriorityScheme);
                    // merge global & primary scopes plugin data to other scopes by default
                    $this->pluginData = $this->globalScopePluginData;
                }
            }
        }
    }

    /**
     * Load virtual types for current scope
     *
     * @return array
     */
    private function loadScopedVirtualTypes()
    {
        $virtualTypes = [];
        foreach ($this->scopePriorityScheme as $scopeCode) {
            if (!isset($this->loadedScopes[$scopeCode])) {
                $data = $this->reader->read($scopeCode) ?: [];
                unset($data['preferences']);
                if (count($data) > 0) {
                    $this->inherited = [];
                    $this->processed = [];
                    $this->merge($data);
                    foreach ($data as $class => $config) {
                        if (isset($config['type'])) {
                            $virtualTypes[] = $class;
                        }
                    }
                }
                $this->loadedScopes[$scopeCode] = true;
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
    private function inheritPlugins($type)
    {
        $type = ltrim($type, '\\');
        if (!isset($this->inherited[$type])) {
            $realType = $this->omConfig->getOriginalInstanceType($type);

            if ($realType !== $type) {
                $plugins = $this->inheritPlugins($realType);
            } elseif ($this->relations->has($type)) {
                $relations = $this->relations->getParents($type);
                $plugins = [];
                foreach ($relations as $relation) {
                    if ($relation) {
                        $relationPlugins = $this->inheritPlugins($relation);
                        if ($relationPlugins) {
                            $plugins = array_replace_recursive($plugins, $relationPlugins);
                        }
                    }
                }
            } else {
                $plugins = [];
            }
            if (isset($this->pluginData[$type])) {
                if (!$plugins) {
                    $plugins = $this->pluginData[$type];
                } else {
                    $plugins = array_replace_recursive($plugins, $this->pluginData[$type]);
                }
            }
            $this->inherited[$type] = null;
            if (is_array($plugins) && count($plugins)) {
                $this->filterPlugins($plugins);
                uasort($plugins, [$this, 'sort']);
                $this->trimInstanceStartingBackslash($plugins);
                $this->inherited[$type] = $plugins;
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
                        $current = $lastPerMethod[$pluginMethod] ?? '__self';
                        $currentKey = $type . '_' . $pluginMethod . '_' . $current;
                        if ($methodTypes & DefinitionInterface::LISTENER_AROUND) {
                            $this->processed[$currentKey][DefinitionInterface::LISTENER_AROUND] = $key;
                            $lastPerMethod[$pluginMethod] = $key;
                        }
                        if ($methodTypes & DefinitionInterface::LISTENER_BEFORE) {
                            $this->processed[$currentKey][DefinitionInterface::LISTENER_BEFORE][] = $key;
                        }
                        if ($methodTypes & DefinitionInterface::LISTENER_AFTER) {
                            $this->processed[$currentKey][DefinitionInterface::LISTENER_AFTER][] = $key;
                        }
                    }
                }
            }
            return $plugins;
        }
        return $this->inherited[$type];
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
                if (isset($this->pluginData[$type])) {
                    $this->pluginData[$type] = array_replace_recursive(
                        $this->pluginData[$type],
                        $typeConfig['plugins']
                    );
                } else {
                    $this->pluginData[$type] = $typeConfig['plugins'];
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
    private function sort($itemA, $itemB)
    {
        return ($itemA['sortOrder'] ?? PHP_INT_MIN) - ($itemB['sortOrder'] ?? PHP_INT_MIN);
    }
}
