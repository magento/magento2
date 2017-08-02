<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Filter;

/**
 * Magento filter factory abstract
 * @since 2.0.0
 */
abstract class AbstractFactory implements FactoryInterface
{
    /**
     * Set of filters
     *
     * @var array
     * @since 2.0.0
     */
    protected $invokableClasses = [];

    /**
     * Whether or not to share by default; default to false
     *
     * @var bool
     * @since 2.0.0
     */
    protected $shareByDefault = true;

    /**
     * Shared instances, by default is shared
     *
     * @var array
     * @since 2.0.0
     */
    protected $shared = [];

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     * @since 2.0.0
     */
    protected $objectManager;

    /**
     * @var \Zend_Filter_Interface[]
     * @since 2.0.0
     */
    protected $sharedInstances = [];

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManger
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
