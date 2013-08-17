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
class Magento_ObjectManager_Config_Config implements Magento_ObjectManager_Config
{
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
    protected $_pluginConfig = array();

    /**
     * List of relations
     *
     * @var Magento_ObjectManager_Relations
     */
    protected $_relations;

    /**
     * @param Magento_ObjectManager_Relations $relations
     */
    public function __construct(Magento_ObjectManager_Relations $relations = null)
    {
        $this->_relations = $relations ?: new Magento_ObjectManager_Relations_Runtime();
    }

    /**
     * Set class relations
     *
     * @param Magento_ObjectManager_Relations $relations
     */
    public function setRelations(Magento_ObjectManager_Relations $relations)
    {
        $this->_relations = $relations;
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
        if (isset($this->_arguments[$type])) {
            $arguments = array_replace($this->_arguments[$type], $arguments);
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
     * @throws LogicException
     */
    public function getPreference($type)
    {
        $preferencePath = array();
        while (isset($this->_preferences[$type])) {
            if (isset($preferencePath[$this->_preferences[$type]])) {
                throw new LogicException(
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
     * Collect plugins for type
     *
     * @param string $type
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _collectPlugins($type)
    {
        if (!isset($this->_pluginConfig[$type])) {
            if (substr($type, -5) === 'Proxy') {
                $this->_pluginConfig[$type] = false;
                return false;
            }
            if (isset($this->_virtualTypes[$type])) {
                $plugins = $this->_collectPlugins($this->_virtualTypes[$type]);
            } else {
                $relations = $this->_relations->getParents($type);
                $plugins = array();
                foreach ($relations as $relation) {
                    if ($relation) {
                        $relationPlugins = $this->_collectPlugins($relation);
                        if ($relationPlugins) {
                            $plugins = array_replace($plugins, $relationPlugins);
                        }
                    }
                }
            }
            if (isset($this->_plugins[$type])) {
                if ($plugins && count($plugins)) {
                    $plugins = array_replace_recursive($plugins, $this->_plugins[$type]);
                } else {
                    $plugins = $this->_plugins[$type];
                }
            }
            if (!is_array($plugins) || !count($plugins)) {
                $plugins = false;
            } else {
                usort($plugins, array($this, '_sort'));
            }
            $this->_pluginConfig[$type] = $plugins;
        }
        return $this->_pluginConfig[$type];
    }

    /**
     * Check whether type has configured plugins
     *
     * @param string $type
     * @return bool
     */
    public function hasPlugins($type)
    {
        return $this->_collectPlugins($type) !== false;
    }

    /**
     * Retrieve list of plugins
     *
     * @param string $type
     * @return array
     */
    public function getPlugins($type)
    {
        return $this->_pluginConfig[$type];
    }

    /**
     * Sorting items
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
     * Extend configuration
     *
     * @param array $configuration
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function extend(array $configuration)
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
                        if (isset($this->_arguments[$key])) {
                            $this->_arguments[$key] = array_replace($this->_arguments[$key], $curConfig['parameters']);
                        } else {
                            $this->_arguments[$key] = $curConfig['parameters'];
                        }
                    }
                    if (isset($curConfig['shared'])) {
                        if (!$curConfig['shared'] || $curConfig['shared'] == 'false') {
                            $this->_nonShared[$key] = 1;
                        } else {
                            unset($this->_nonShared[$key]);
                        }
                    }
                    if (isset($curConfig['plugins'])) {
                        if (!empty($this->_pluginConfig)) {
                            $this->_pluginConfig = array();
                        }
                        if (isset($this->_plugins[$key])) {
                            $this->_plugins[$key] = array_replace($this->_plugins[$key], $curConfig['plugins']);
                        } else {
                            $this->_plugins[$key] = $curConfig['plugins'];
                        }
                    }
                    break;
            }
        }
    }
}
