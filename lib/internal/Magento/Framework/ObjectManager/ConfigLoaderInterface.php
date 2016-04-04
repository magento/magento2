<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\ObjectManager;

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
