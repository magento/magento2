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
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\ObjectManager\Config;

use Magento\Framework\ObjectManager\ConfigCache;
use Magento\Framework\ObjectManager\Definition;
use Magento\Framework\ObjectManager\Relations;

class Config implements \Magento\Framework\ObjectManager\Config
{
    /**
     * Config cache
     *
     * @var ConfigCache
     */
    protected $_cache;

    /**
     * Class definitions
     *
     * @var \Magento\Framework\ObjectManager\Definition
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
     * List of relations
     *
     * @var Relations
     */
    protected $_relations;

    /**
     * List of merged arguments
     *
     * @var array
     */
    protected $_mergedArguments;

    /**
     * @param Relations $relations
     * @param Definition $definitions
     */
    public function __construct(Relations $relations = null, Definition $definitions = null)
    {
        $this->_relations = $relations ?: new \Magento\Framework\ObjectManager\Relations\Runtime();
        $this->_definitions = $definitions ?: new \Magento\Framework\ObjectManager\Definition\Runtime();
    }

    /**
     * Set class relations
     *
     * @param Relations $relations
     * @return void
     */
    public function setRelations(Relations $relations)
    {
        $this->_relations = $relations;
    }

    /**
     * Set cache instance
     *
     * @param ConfigCache $cache
     * @return void
     */
    public function setCache(ConfigCache $cache)
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
        return isset($this->_mergedArguments[$type])
            ? $this->_mergedArguments[$type]
            : $this->_collectConfiguration($type);
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
        $type = ltrim($type, '\\');
        $preferencePath = array();
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
                            $this->_mergedArguments = array();
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
                $this->_currentCacheKey = md5(
                    serialize(array($this->_arguments, $this->_nonShared, $this->_preferences, $this->_virtualTypes))
                );
            }
            $key = md5($this->_currentCacheKey . serialize($configuration));
            $cached = $this->_cache->get($key);
            if ($cached) {
                list($this->_arguments,
                    $this
                    ->_nonShared,
                    $this
                    ->_preferences,
                    $this
                    ->_virtualTypes,
                    $this
                    ->_mergedArguments) = $cached;
            } else {
                $this->_mergeConfiguration($configuration);
                if (!$this->_mergedArguments) {
                    foreach ($this->_definitions->getClasses() as $class) {
                        $this->_collectConfiguration($class);
                    }
                }
                $this->_cache->save(
                    array(
                        $this->_arguments,
                        $this->_nonShared,
                        $this->_preferences,
                        $this->_virtualTypes,
                        $this->_mergedArguments
                    ),
                    $key
                );
            }
            $this->_currentCacheKey = $key;
        } else {
            $this->_mergeConfiguration($configuration);
        }
    }
}
