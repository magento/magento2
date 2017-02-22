<?php
/**
 * Interception config. Responsible for providing list of plugins configured for instance
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Interception\Config;

class Config implements \Magento\Framework\Interception\ConfigInterface
{
    /**
     * Type configuration
     *
     * @var \Magento\Framework\Interception\ObjectManager\ConfigInterface
     */
    protected $_omConfig;

    /**
     * Class relations info
     *
     * @var \Magento\Framework\ObjectManager\RelationsInterface
     */
    protected $_relations;

    /**
     * List of interceptable classes
     *
     * @var \Magento\Framework\ObjectManager\DefinitionInterface
     */
    protected $_classDefinitions;

    /**
     * Cache
     *
     * @var \Magento\Framework\Cache\FrontendInterface
     */
    protected $_cache;

    /**
     * Cache identifier
     *
     * @var string
     */
    protected $_cacheId;

    /**
     * Configuration reader
     *
     * @var \Magento\Framework\Config\ReaderInterface
     */
    protected $_reader;

    /**
     * Inherited list of intercepted types
     *
     * @var array
     */
    protected $_intercepted = [];

    /**
     * List of class types that can not be pluginized
     *
     * @var array
     */
    protected $_serviceClassTypes = ['Interceptor'];

    /**
     * @var \Magento\Framework\Config\ScopeListInterface
     */
    protected $_scopeList;

    /**
     * @param \Magento\Framework\Config\ReaderInterface $reader
     * @param \Magento\Framework\Config\ScopeListInterface $scopeList
     * @param \Magento\Framework\Cache\FrontendInterface $cache
     * @param \Magento\Framework\ObjectManager\RelationsInterface $relations
     * @param \Magento\Framework\Interception\ObjectManager\ConfigInterface $omConfig
     * @param \Magento\Framework\ObjectManager\DefinitionInterface $classDefinitions
     * @param string $cacheId
     */
    public function __construct(
        \Magento\Framework\Config\ReaderInterface $reader,
        \Magento\Framework\Config\ScopeListInterface $scopeList,
        \Magento\Framework\Cache\FrontendInterface $cache,
        \Magento\Framework\ObjectManager\RelationsInterface $relations,
        \Magento\Framework\Interception\ObjectManager\ConfigInterface $omConfig,
        \Magento\Framework\ObjectManager\DefinitionInterface $classDefinitions,
        $cacheId = 'interception'
    ) {
        $this->_omConfig = $omConfig;
        $this->_relations = $relations;
        $this->_classDefinitions = $classDefinitions;
        $this->_cache = $cache;
        $this->_cacheId = $cacheId;
        $this->_reader = $reader;
        $this->_scopeList = $scopeList;

        $intercepted = $this->_cache->load($this->_cacheId);
        if ($intercepted !== false) {
            $this->_intercepted = unserialize($intercepted);
        } else {
            $this->initialize($this->_classDefinitions->getClasses());
        }
    }

    /**
     * Initialize interception config
     *
     * @param array $classDefinitions
     * @return void
     */
    public function initialize($classDefinitions = [])
    {
        $this->_cache->clean(\Zend_Cache::CLEANING_MODE_MATCHING_TAG, [$this->_cacheId]);
        $config = [];
        foreach ($this->_scopeList->getAllScopes() as $scope) {
            $config = array_replace_recursive($config, $this->_reader->read($scope));
        }
        unset($config['preferences']);
        foreach ($config as $typeName => $typeConfig) {
            if (!empty($typeConfig['plugins'])) {
                $this->_intercepted[ltrim($typeName, '\\')] = true;
            }
        }
        foreach ($config as $typeName => $typeConfig) {
            $this->hasPlugins($typeName);
        }
        foreach ($classDefinitions as $class) {
            $this->hasPlugins($class);
        }
        $this->_cache->save(serialize($this->_intercepted), $this->_cacheId);
    }

    /**
     * Process interception inheritance
     *
     * @param string $type
     * @return bool
     */
    protected function _inheritInterception($type)
    {
        $type = ltrim($type, '\\');
        if (!isset($this->_intercepted[$type])) {
            $realType = $this->_omConfig->getOriginalInstanceType($type);
            if ($type !== $realType) {
                if ($this->_inheritInterception($realType)) {
                    $this->_intercepted[$type] = true;
                    return true;
                }
            } else {
                $parts = explode('\\', $type);
                if (!in_array(end($parts), $this->_serviceClassTypes) && $this->_relations->has($type)) {
                    $relations = $this->_relations->getParents($type);
                    foreach ($relations as $relation) {
                        if ($relation && $this->_inheritInterception($relation)) {
                            $this->_intercepted[$type] = true;
                            return true;
                        }
                    }
                }
            }
            $this->_intercepted[$type] = false;
        }
        return $this->_intercepted[$type];
    }

    /**
     * {@inheritdoc}
     */
    public function hasPlugins($type)
    {
        if (isset($this->_intercepted[$type])) {
            return $this->_intercepted[$type];
        }
        return $this->_inheritInterception($type);
    }
}
