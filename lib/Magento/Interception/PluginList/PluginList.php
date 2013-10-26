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
 * @copyright Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Interception\PluginList;

use Zend\Soap\Exception\InvalidArgumentException;

class PluginList
    extends \Magento\Config\Data\Scoped
    implements \Magento\Interception\PluginList
{
    /**
     * Type config
     *
     * @var \Magento\ObjectManager\Config
     */
    protected $_omConfig;

    /**
     * Class relations information provider
     *
     * @var \Magento\ObjectManager\Relations
     */
    protected $_relations;

    /**
     * List of interception methods per plugin
     *
     * @var \Magento\Interception\Definition
     */
    protected $_definitions;

    /**
     * List of interceptable application classes
     *
     * @var \Magento\ObjectManager\Definition\Compiled
     */
    protected $_classDefinitions;

    /**
     * Scope inheritance scheme
     *
     * @var array
     */
    protected $_scopePriorityScheme = array('global');

    /**
     * @param \Magento\Config\ReaderInterface $reader
     * @param \Magento\Config\ScopeInterface $configScope
     * @param \Magento\Config\CacheInterface $cache
     * @param \Magento\ObjectManager\Relations $relations
     * @param \Magento\ObjectManager\Config $omConfig
     * @param \Magento\Interception\Definition $definitions
     * @param array $scopePriorityScheme
     * @param \Magento\ObjectManager\Definition\Compiled $classDefinitions
     * @param string $cacheId
     */
    public function __construct(
        \Magento\Config\ReaderInterface $reader,
        \Magento\Config\ScopeInterface $configScope,
        \Magento\Config\CacheInterface $cache,
        \Magento\ObjectManager\Relations $relations,
        \Magento\ObjectManager\Config $omConfig,
        \Magento\Interception\Definition $definitions,
        array $scopePriorityScheme,
        \Magento\ObjectManager\Definition\Compiled $classDefinitions = null,
        $cacheId = 'plugins'
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
