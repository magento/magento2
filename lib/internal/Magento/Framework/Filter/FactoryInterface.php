<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Filter;

/**
 * Magento filter factory interface
 * @since 2.0.0
 */
interface FactoryInterface
{
    /**
     * Check is it possible to create a filter by given name
     *
     * @param string $alias
     * @return bool
     * @since 2.0.0
     */
    public function canCreateFilter($alias);

    /**
     * Check is shared filter
     *
     * @param string $class
     * @return bool
     * @since 2.0.0
     */
    public function isShared($class);

    /**
     * Create a filter by given name
     *
     * @param string $alias
     * @param array $arguments
     * @return \Zend_Filter_Interface
     * @since 2.0.0
     */
    public function createFilter($alias, array $arguments = []);
}
