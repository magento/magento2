<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Component;

/**
 * @author Josh Di Fabio <joshdifabio@gmail.com>
 */
interface ComponentRegistryInterface
{
    /**
     * Get list of registered Magento components
     *
     * Returns an array where key is fully-qualified component name and value is absolute path to component
     *
     * @return array
     */
    public function getPaths();

    /**
     * Get path of a component if it is already registered
     *
     * @param string $componentName
     * @return null|string
     */
    public function getPath($componentName);
}
