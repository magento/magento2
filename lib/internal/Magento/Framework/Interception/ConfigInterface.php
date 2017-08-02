<?php
/**
 * Interception config. Tells whether plugins have been added for type.
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Interception;

/**
 * Interface \Magento\Framework\Interception\ConfigInterface
 *
 * @since 2.0.0
 */
interface ConfigInterface
{
    /**
     * Check whether type has configured plugins
     *
     * @param string $type
     * @return bool
     * @since 2.0.0
     */
    public function hasPlugins($type);

    /**
     * Initialize interception config
     *
     * @param array $classDefinitions
     * @return void
     * @since 2.0.0
     */
    public function initialize($classDefinitions = []);
}
