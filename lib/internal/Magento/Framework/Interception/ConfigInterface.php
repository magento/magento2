<?php
/**
 * Interception config. Tells whether plugins have been added for type.
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Interception;

/**
 * Interface \Magento\Framework\Interception\ConfigInterface
 *
 * @api
 */
interface ConfigInterface
{
    /**
     * Check whether type has configured plugins
     *
     * @param string $type
     * @return bool
     */
    public function hasPlugins($type);

    /**
     * Initialize interception config
     *
     * @param array $classDefinitions
     * @return void
     */
    public function initialize($classDefinitions = []);
}
