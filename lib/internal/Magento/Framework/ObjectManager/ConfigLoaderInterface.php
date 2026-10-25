<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\ObjectManager;

/**
 * Interface \Magento\Framework\ObjectManager\ConfigLoaderInterface
 *
 * @api
 */
interface ConfigLoaderInterface
{
    /**
     * Load modules DI configuration
     *
     * @param string $area
     * @return array
     */
    public function load($area);
}
