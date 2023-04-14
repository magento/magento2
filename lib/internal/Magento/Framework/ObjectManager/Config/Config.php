<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\ObjectManager\Config;

use Magento\Framework\ObjectManager\ConfigCacheInterface;
use Magento\Framework\ObjectManager\ConfigInterface;
use Magento\Framework\ObjectManager\DefinitionInterface;
use Magento\Framework\ObjectManager\Helper\SortItems as SortItemsHelper;
use Magento\Framework\ObjectManager\RelationsInterface;
use Magento\Framework\Serialize\SerializerInterface;

class Config implements ConfigInterface
{
    /**
     * Config cache
     *
     * @var ConfigCacheInterface
     */
    protected $_cache;

    /**
     * Class definitions
     *
     * @var DefinitionInterface
     */
    protected $_definitions;

    /**
     *
     * @var string
     */
    protected $_currentCacheKey;

    /**
     * Interface preferences
     *
     * @var array
     */
    protected $_preferences = [];

    /**
     *
     * @var array
     */
    protected $_virtualTypes = [];

    /**
     * Instance arguments
     *
     * @var array
     */
    protected $_arguments = [];

    /**
     * Type shareability
     *
     * @var array
     */
    protected $_nonShared = [];

    /**
     * List of relations
     *
     * @var RelationsInterface
     */
    protected $_relations;

    /**
     * List of merged arguments
     *
     * @var array
     */
    protected $_mergedArguments;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var SortItemsHelper
     */
    private SortItemsHelper $sortItemsHelper;

    /**
     * @param RelationsInterface|null $relations
     * @param DefinitionInterface|null $definitions
     * @param SortItemsHelper|null $sortItemsHelper
     */
    public function __construct(
        RelationsInterface $relations = null,
        DefinitionInterface $definitions = null,
        SortItemsHelper $sortItemsHelper = null
    ) {
        $this->_relations = $relations ?: new \Magento\Framework\ObjectManager\Relations\Runtime();
        $this->_definitions = $definitions ?: new \Magento\Framework\ObjectManager\Definition\Runtime();
        $this->sortItemsHelper = $sortItemsHelper ?: new \Magento\Framework\ObjectManager\Helper\SortItems();
    }

    /**
     * Set class relations
     *
     * @param RelationsInterface $relations
     * @return void
     */
    public function setRelations(RelationsInterface $relations)
    {
        $this->_relations = $relations;
    }

    /**
     * Set cache instance
     *
     * @param ConfigCacheInterface $cache
     * @return void
     */
    public function setCache(ConfigCacheInterface $cache)
    {
        $this->_cache = $cache;
    }

    /**
     * Retrieve list of arguments per type
     *
     * @param string $type
     * @return array
     */
    public function getArguments($type)
    {
        if (isset($this->_mergedArguments[$type])) {
            return $this->_mergedArguments[$type];
        }
        return $this->_collectConfiguration($type);
    }

    /**
     * Check whether type is shared
     *
     * @param string $type
     * @return bool
     */
    public function isShared($type)
    {
        return !isset($this->_nonShared[$type]);
    }

    /**
     * Retrieve instance type
     *
     * @param string $instanceName
     * @return mixed
     */
    public function getInstanceType($instanceName)
    {
        while (isset($this->_virtualTypes[$instanceName])) {
            $instanceName = $this->_virtualTypes[$instanceName];
        }
        return $instanceName;
    }

    /**
     * Retrieve preference for type
     *
     * @param string $type
     * @return string
     * @throws \LogicException
     */
    public function getPreference($type)
    {
        $type = $type !== null ? ltrim($type, '\\') : '';
        $preferencePath = [];
        while (isset($this->_preferences[$type])) {
            if (isset($preferencePath[$this->_preferences[$type]])) {
                throw new \LogicException(
                    'Circular type preference: ' .
                    $type .
                    ' relates to ' .
                    $this->_preferences[$type] .
                    ' and viceversa.'
                );
            }
            $type = $this->_preferences[$type];
            $preferencePath[$type] = 1;
        }
        return $type;
    }

    /**
     * Collect parent types configuration for requested type
     *
     * @param string $type
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _collectConfiguration($type): array
    {
        if (!isset($this->_mergedArguments[$type])) {
            if (isset($this->_virtualTypes[$type])) {
                $arguments = $this->_collectConfiguration($this->_virtualTypes[$type]);
                $arguments = $this->sortItemsHelper->sortItems($arguments);
            } elseif ($this->_relations->has($type)) {
                $relations = $this->_relations->getParents($type);
                $arguments = [];
                foreach ($relations as $relation) {
                    if ($relation) {
                        $relationArguments = $this->_collectConfiguration($relation);
                        if ($relationArguments) {
                            $arguments = array_replace($arguments, $relationArguments);
                            $arguments = $this->sortItemsHelper->sortItems($arguments);
                        }
                    }
                }
            } else {
                $arguments = [];
            }

            if (isset($this->_arguments[$type])) {
                if ($arguments && count($arguments)) {
                    $arguments = array_replace_recursive($arguments, $this->_arguments[$type]);
                    $arguments = $this->sortItemsHelper->sortItems($arguments);
                } else {
                    $arguments = $this->_arguments[$type];
                }
            }
            $this->_mergedArguments[$type] = $arguments;
            return $arguments;
        }
        return $this->_mergedArguments[$type];
    }

    /**
     * Merge configuration
     *
     * @param array $configuration
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _mergeConfiguration(array $configuration)
    {
        foreach ($configuration as $key => $curConfig) {
            switch ($key) {
                case 'preferences':
                    foreach ($curConfig as $for => $to) {
                        $this->_preferences[ltrim($for, '\\')] = ltrim($to, '\\');
                    }
                    break;

                default:
                    $key = ltrim($key, '\\');
                    if (isset($curConfig['type'])) {
                        $this->_virtualTypes[$key] = ltrim($curConfig['type'], '\\');
                    }
                    if (isset($curConfig['arguments'])) {
                        if (!empty($this->_mergedArguments)) {
                            $this->_mergedArguments = [];
                        }
                        if (isset($this->_arguments[$key])) {
                            $this->_arguments[$key] = array_replace($this->_arguments[$key], $curConfig['arguments']);
                        } else {
                            $this->_arguments[$key] = $curConfig['arguments'];
                        }
                    }
                    if (isset($curConfig['shared'])) {
                        if (!$curConfig['shared']) {
                            $this->_nonShared[$key] = 1;
                        } else {
                            unset($this->_nonShared[$key]);
                        }
                    }
                    break;
            }
        }
    }

    /**
     * Extend configuration
     *
     * @param array $configuration
     * @return void
     */
    public function extend(array $configuration)
    {
        if ($this->_cache) {
            if (!$this->_currentCacheKey) {
                // md5() here is not for cryptographic use.
                // phpcs:ignore Magento2.Security.InsecureFunction
                $this->_currentCacheKey = md5(
                    $this->getSerializer()->serialize(
                        [$this->_arguments, $this->_nonShared, $this->_preferences, $this->_virtualTypes]
                    )
                );
            }
            // md5() here is not for cryptographic use.
            // phpcs:ignore Magento2.Security.InsecureFunction
            $key = md5($this->_currentCacheKey . $this->getSerializer()->serialize($configuration));
            $cached = $this->_cache->get($key);
            if ($cached) {
                list(
                    $this->_arguments,
                    $this->_nonShared,
                    $this->_preferences,
                    $this->_virtualTypes,
                    $this->_mergedArguments
                ) = $cached;
            } else {
                $this->_mergeConfiguration($configuration);
                if (!$this->_mergedArguments) {
                    foreach ($this->_definitions->getClasses() as $class) {
                        $this->_collectConfiguration($class);
                    }
                }
                $this->_cache->save(
                    [
                        $this->_arguments,
                        $this->_nonShared,
                        $this->_preferences,
                        $this->_virtualTypes,
                        $this->_mergedArguments,
                    ],
                    $key
                );
            }
            $this->_currentCacheKey = $key;
        } else {
            $this->_mergeConfiguration($configuration);
        }
    }

    /**
     * Returns list of virtual types
     *
     * @return array
     */
    public function getVirtualTypes()
    {
        return $this->_virtualTypes;
    }

    /**
     * Returns list on preferences
     *
     * @return array
     */
    public function getPreferences()
    {
        return $this->_preferences;
    }

    /**
     * Get serializer
     *
     * @return SerializerInterface
     * @deprecated 101.0.0
     */
    private function getSerializer()
    {
        if ($this->serializer === null) {
            $this->serializer = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(SerializerInterface::class);
        }
        return $this->serializer;
    }
}
