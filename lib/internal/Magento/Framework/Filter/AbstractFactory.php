<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Filter;

/**
 * Magento filter factory abstract
 */
abstract class AbstractFactory implements FactoryInterface
{
    /**
     * Set of filters
     *
     * @var array
     */
    protected $invokableClasses = [];

    /**
     * Whether or not to share by default; default to false
     *
     * @var bool
     */
    protected $shareByDefault = true;

    /**
     * Shared instances, by default is shared
     *
     * @var array
     */
    protected $shared = [];

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Zend_Filter_Interface[]
     */
    protected $sharedInstances = [];

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManger
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManger)
    {
        $this->objectManager = $objectManger;
    }

    /**
     * Check is it possible to create a filter by given name
     *
     * @param string $alias
     * @return bool
     */
    public function canCreateFilter($alias)
    {
        return array_key_exists($alias, $this->invokableClasses);
    }

    /**
     * Check is shared filter
     *
     * @param string $class
     * @return bool
     */
    public function isShared($class)
    {
        return isset($this->shared[$class]) ? $this->shared[$class] : $this->shareByDefault;
    }

    /**
     * Create a filter by given name
     *
     * @param string $alias
     * @param array $arguments
     * @return \Zend_Filter_Interface
     */
    public function createFilter($alias, array $arguments = [])
    {
        $addToShared = !$arguments || isset(
            $this->sharedInstances[$alias]
        ) xor $this->isShared(
            $this->invokableClasses[$alias]
        );

        if (!isset($this->sharedInstances[$alias])) {
            $filter = $this->objectManager->create($this->invokableClasses[$alias], $arguments);
        } else {
            $filter = $this->sharedInstances[$alias];
        }

        if ($addToShared) {
            $this->sharedInstances[$alias] = $filter;
        }

        return $filter;
    }
}
