<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\ObjectManager;

/**
 * Interface \Magento\Framework\ObjectManager\ConfigLoaderInterface
 *
 * @since 2.0.0
 */
interface ConfigLoaderInterface
{
    /**
     * Load modules DI configuration
     *
     * @param string $area
     * @return array
     * @since 2.0.0
     */
    public function load($area);
}
