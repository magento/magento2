<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework;

/**
 * @api
 * @since 100.0.2
 */
interface ObjectManagerInterface
{
    /**
     * Create new object instance
     *
     * @template T
     * @param class-string<T> $type
     * @param array $arguments
     * @return T
     */
    public function create($type, array $arguments = []);

    /**
     * Retrieve cached object instance
     *
     * @template T
     * @param class-string<T> $type
     * @return T
     */
    public function get($type);

    /**
     * Configure object manager
     *
     * @param array $configuration
     * @return void
     */
    public function configure(array $configuration);
}
