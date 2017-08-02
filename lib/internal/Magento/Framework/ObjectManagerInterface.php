<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework;

/**
 * @api
 * @since 2.0.0
 */
interface ObjectManagerInterface
{
    /**
     * Create new object instance
     *
     * @param string $type
     * @param array $arguments
     * @return mixed
     * @since 2.0.0
     */
    public function create($type, array $arguments = []);

    /**
     * Retrieve cached object instance
     *
     * @param string $type
     * @return mixed
     * @since 2.0.0
     */
    public function get($type);

    /**
     * Configure object manager
     *
     * @param array $configuration
     * @return void
     * @since 2.0.0
     */
    public function configure(array $configuration);
}
