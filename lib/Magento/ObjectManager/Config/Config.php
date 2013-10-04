<?php
/**
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
 * @copyright Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\ObjectManager\Config;

class Config implements \Magento\ObjectManager\Config
{
    /**
     * Config cache
     *
     * @var \Magento\ObjectManager\ConfigCache
     */
    protected $_cache;

    /**
     * Class definitions
     *
     * @var \Magento\ObjectManager\Definition
     */
    protected $_definitions;

    /**
     * Current cache key
     *
     * @var string
     */
    protected $_currentCacheKey;

    /**
     * Interface preferences
     *
     * @var array
     */
    protected $_preferences = array();

    /**
     * Virtual types
     *
     * @var array
     */
    protected $_virtualTypes = array();

    /**
     * Instance arguments
     *
     * @var array
     */
    protected $_arguments = array();

    /**
     * Type shareability
     *
     * @var array
     */
    protected $_nonShared = array();

    /**
     * Plugin configuration
     *
     * @var array
     */
    protected $_plugins = array();

    /**
     * Merged plugin config
     *
     * @var array
     */
    protected $_mergedPlugins = array();

    /**
     * List of relations
     *
     * @var \Magento\ObjectManager\Relations
     */
    protected $_relations;

    /**
     * List of merged arguments
     *
     * @var array
     */
    protected $_mergedArguments;

    /**
     * @param \Magento\ObjectManager\Relations $relations
     * @param \Magento\ObjectManager\Definition $definitions
     */
    public function __construct(
        \Magento\ObjectManager\Relations $relations = null,
        \Magento\ObjectManager\Definition $definitions = null
    ) {
        $this->_relations = $relations ?: new \Magento\ObjectManager\Relations\Runtime();
        $this->_definitions = $definitions ?: new \Magento\ObjectManager\Definition\Runtime();
    }

    /**
     * Set class relations
     *
     * @param \Magento\ObjectManager\Relations $relations
     */
    public function setRelations(\Magento\ObjectManager\Relations $relations)
    {
        $this->_relations = $relations;
    }

    /**
     * Set cache instance
     *
     * @param \Magento\ObjectManager\ConfigCache $cache
     */
    public function setCache(\Magento\ObjectManager\ConfigCache $cache)
    {
        $this->_cache = $cache;
    }

    /**
     * Retrieve list of arguments per type
     *
     * @param string $type
     * @param array $arguments
     * @return array
     */
    public function getArguments($type, $arguments)
    {
        $configuredArguments = isset($this->_mergedArguments[$type])
            ? $this->_mergedArguments[$type]
            : $this->_collectConfiguration($type);

        if (is_array($configuredArguments)) {
            $arguments = array_replace($configuredArguments, $arguments);
        }
        return $arguments;
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
        $preferencePath = array();
        while (isset($this->_preferences[$type])) {
            if (isset($preferencePath[$this->_preferences[$type]])) {
                throw new \LogicException(
                    'Circular type preference: ' . $type . ' relates to '
                    . $this->_preferences[$type] . ' and viceversa.'
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
    protected function _collectConfiguration($type)
    {
        if (!isset($this->_mergedArguments[$type])) {
            if (isset($this->_virtualTypes[$type])) {
                $arguments = $this->_collectConfiguration($this->_virtualTypes[$type]);
            } else if ($this->_relations->has($type)) {
                $relations = $this->_relations->getParents($type);
                $arguments = array();
                foreach ($relations as $relation) {
                    if ($relation) {
                        $relationArguments = $this->_collectConfiguration($relation);
                        if ($relationArguments) {
                            $arguments = array_replace($arguments, $relationArguments);
                        }
                    }
                }
            } else {
                $arguments = array();
            }

            if (isset($this->_arguments[$type])) {
                if ($arguments && count($arguments)) {
                    $arguments = array_replace_recursive($arguments, $this->_arguments[$type]);
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
     */
    public function _mergeConfiguration(array $configuration)
    {
        foreach ($configuration as $key => $curConfig) {
            switch ($key) {
                case 'preferences':
                    $this->_preferences = array_replace($this->_preferences, $curConfig);
                    break;

                default:
                    if (isset($curConfig['type'])) {
                        $this->_virtualTypes[$key] = $curConfig['type'];
                    }
                    if (isset($curConfig['parameters'])) {
                        if (!empty($this->_mergedArguments)) {
                            $this->_mergedArguments = array();
                        }
                        if (isset($this->_arguments[$key])) {
                            $this->_arguments[$key] = array_replace($this->_arguments[$key], $curConfig['parameters']);
                        } else {
                            $this->_arguments[$key] = $curConfig['parameters'];
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
     */
    public function extend(array $configuration)
    {
        if ($this->_cache) {
            if (!$this->_currentCacheKey) {
                $this->_currentCacheKey = md5(serialize(array(
                    $this->_arguments, $this->_nonShared, $this->_preferences, $this->_virtualTypes
                )));
            }
            $key = md5($this->_currentCacheKey . serialize($configuration));
            $cached = $this->_cache->get($key);
            if ($cached) {
                list(
                    $this->_arguments, $this->_nonShared, $this->_preferences,
                    $this->_virtualTypes, $this->_mergedArguments
                ) = $cached;
            } else {
                $this->_mergeConfiguration($configuration);
                if (!$this->_mergedArguments) {
                    foreach ($this->_definitions->getClasses() as $class) {
                        $this->_collectConfiguration($class);
                    }
                }
                $this->_cache->save(array(
                    $this->_arguments, $this->_nonShared, $this->_preferences, $this->_virtualTypes,
                    $this->_mergedArguments
                ), $key);
            }
            $this->_currentCacheKey = $key;
        } else {
            $this->_mergeConfiguration($configuration);
        }
    }
}
