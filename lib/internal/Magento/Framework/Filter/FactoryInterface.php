<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Filter;

use Laminas\Filter\FilterInterface;

/**
 * Magento filter factory interface
 *
 * @api
 */
interface FactoryInterface
{
    /**
     * Check is it possible to create a filter by given name
     *
     * @param string $alias
     * @return bool
     */
    public function canCreateFilter($alias);

    /**
     * Check is shared filter
     *
     * @param string $class
     * @return bool
     */
    public function isShared($class);

    /**
     * Create a filter by given name
     *
     * @param string $alias
     * @param array $arguments
     * @return FilterInterface
     */
    public function createFilter($alias, array $arguments = []);
}
