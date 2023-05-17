<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Interception;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Config\ReaderInterface;
use Magento\Framework\Config\ScopeInterface;
use Magento\Framework\Interception\ObjectManager\ConfigInterface;
use Magento\Framework\ObjectManager\DefinitionInterface as ClassDefinitions;
use Magento\Framework\ObjectManager\RelationsInterface;
use Psr\Log\LoggerInterface;

/**
 * Plugin list configuration writer and loader for scopes.
 */
class PluginListGenerator implements ConfigWriterInterface, ConfigLoaderInterface
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
     * @var DirectoryList
     */
    private $directoryList;

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
     * @param DirectoryList $directoryList
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
        DirectoryList $directoryList,
        array $scopePriorityScheme = ['global']
    ) {
        $this->reader = $reader;
        $this->scopeConfig = $scopeConfig;
        $this->omConfig = $omConfig;
        $this->relations = $relations;
        $this->definitions = $definitions;
        $this->classDefinitions = $classDefinitions;
        $this->logger = $logger;
        $this->directoryList = $directoryList;
        $this->scopePriorityScheme = $scopePriorityScheme;
    }

    /**
     * @inheritdoc
     */
    public function write(array $scopes): void
    {
        foreach ($scopes as $scope) {
            $this->scopeConfig->setCurrentScope($scope);
            if (false === isset($this->loadedScopes[$scope])) {
                if (false === in_array($scope, $this->scopePriorityScheme, true)) {
                    $this->scopePriorityScheme[] = $scope;
                }
                $cacheId = implode('|', $this->scopePriorityScheme) . "|" . $this->cacheId;
                [
                    $virtualTypes,
                    $this->scopePriorityScheme,
                    $this->loadedScopes,
                    $this->pluginData,
                    $this->inherited,
                    $this->processed
                ] = $this->loadScopedVirtualTypes(
                    $this->scopePriorityScheme,
                    $this->loadedScopes,
                    $this->pluginData,
                    $this->inherited,
                    $this->processed
                );
                foreach ($virtualTypes as $class) {
                    $this->inheritPlugins($class, $this->pluginData, $this->inherited, $this->processed);
                }
                foreach (array_keys($this->pluginData) as $className) {
                    $this->inheritPlugins($className, $this->pluginData, $this->inherited, $this->processed);
                }
                foreach ($this->getClassDefinitions() as $class) {
                    $this->inheritPlugins($class, $this->pluginData, $this->inherited, $this->processed);
                }
                $this->writeConfig(
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
     * @inheritdoc
     */
    public function load(string $cacheId): array
    {
        $file = $this->directoryList->getPath(DirectoryList::GENERATED_METADATA) . '/' . $cacheId . '.' . 'php';
        if (file_exists($file)) {
            return include $file;
        }

        return [];
    }

    /**
     * Load virtual types for current scope
     *
     * @param array $scopePriorityScheme
     * @param array $loadedScopes
     * @param array|null $pluginData
     * @param array $inherited
     * @param array $processed
     * @return array
     */
    public function loadScopedVirtualTypes($scopePriorityScheme, $loadedScopes, $pluginData, $inherited, $processed)
    {
        $virtualTypes = [];
        foreach ($scopePriorityScheme as $scopeCode) {
            if (!isset($loadedScopes[$scopeCode])) {
                $data = $this->reader->read($scopeCode) ?: [];
                unset($data['preferences']);
                if (count($data) > 0) {
                    $pluginData = $this->merge($data, $pluginData);
                    foreach ($data as $class => $config) {
                        if (isset($config['type'])) {
                            $virtualTypes[] = $class;
                        }
                    }
                }
                $inherited = [];
                $processed = [];
                $loadedScopes[$scopeCode] = true;
            }
            if ($this->isCurrentScope($scopeCode)) {
                break;
            }
        }
        return [$virtualTypes, $scopePriorityScheme, $loadedScopes, $pluginData, $inherited, $processed];
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
     * @param array $pluginData
     * @param array $inherited
     * @param array $processed
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function inheritPlugins($type, &$pluginData, &$inherited, &$processed)
    {
        $type = $type !== null ? ltrim($type, '\\') : '';
        if (!isset($inherited[$type])) {
            $realType = $this->omConfig->getOriginalInstanceType($type);

            if ($realType !== $type) {
                $plugins = $this->inheritPlugins($realType, $pluginData, $inherited, $processed);
            } elseif ($this->relations->has($type)) {
                $relations = $this->relations->getParents($type);
                $plugins = [];
                foreach ($relations as $relation) {
                    if ($relation) {
                        $relationPlugins = $this->inheritPlugins($relation, $pluginData, $inherited, $processed);
                        if ($relationPlugins) {
                            $plugins = array_replace_recursive($plugins, $relationPlugins);
                        }
                    }
                }
            } else {
                $plugins = [];
            }
            if (isset($pluginData[$type])) {
                if (!$plugins) {
                    $plugins = $pluginData[$type];
                } else {
                    $plugins = array_replace_recursive($plugins, $pluginData[$type]);
                }
            }
            $inherited[$type] = null;
            if (is_array($plugins) && count($plugins)) {
                $this->filterPlugins($plugins);
                uasort($plugins, function ($itemA, $itemB) {
                    return ($itemA['sortOrder'] ?? PHP_INT_MIN) - ($itemB['sortOrder'] ?? PHP_INT_MIN);
                });
                $this->trimInstanceStartingBackslash($plugins);
                $inherited[$type] = $plugins;
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
                            $processed[$currentKey][DefinitionInterface::LISTENER_AROUND] = $key;
                            $lastPerMethod[$pluginMethod] = $key;
                        }
                        if ($methodTypes & DefinitionInterface::LISTENER_BEFORE) {
                            $processed[$currentKey][DefinitionInterface::LISTENER_BEFORE][] = $key;
                        }
                        if ($methodTypes & DefinitionInterface::LISTENER_AFTER) {
                            $processed[$currentKey][DefinitionInterface::LISTENER_AFTER][] = $key;
                        }
                    }
                }
            }
            return $plugins;
        }
        return $inherited[$type];
    }

    /**
     * Trims starting backslash from plugin instance name
     *
     * @param array $plugins
     * @return void
     */
    public function trimInstanceStartingBackslash(&$plugins)
    {
        foreach ($plugins as &$plugin) {
            $plugin['instance'] = ltrim($plugin['instance'] ?? '', '\\');
        }
    }

    /**
     * Remove from list not existing plugins
     *
     * @param array $plugins
     * @return void
     */
    public function filterPlugins(array &$plugins)
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
     * @param array|null $pluginData
     * @return array
     */
    public function merge(array $config, $pluginData)
    {
        foreach ($config as $type => $typeConfig) {
            if (isset($typeConfig['plugins'])) {
                $type = ltrim($type, '\\');
                if (isset($pluginData[$type])) {
                    $pluginData[$type] = array_replace_recursive($pluginData[$type], $typeConfig['plugins']);
                } else {
                    $pluginData[$type] = $typeConfig['plugins'];
                }
            }
        }

        return $pluginData;
    }

    /**
     * Writes config in storage
     *
     * @param string $key
     * @param array $config
     * @return void
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    private function writeConfig(string $key, array $config)
    {
        $this->initialize();
        $configuration = sprintf('<?php return %s;', var_export($config, true));
        file_put_contents(
            $this->directoryList->getPath(DirectoryList::GENERATED_METADATA) . '/' . $key  . '.php',
            $configuration
        );
    }

    /**
     * Initializes writer
     *
     * @return void
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    private function initialize()
    {
        if (!file_exists($this->directoryList->getPath(DirectoryList::GENERATED_METADATA))) {
            mkdir($this->directoryList->getPath(DirectoryList::GENERATED_METADATA));
        }
    }
}
