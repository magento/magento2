<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Component;

/**
 * @author Josh Di Fabio <joshdifabio@gmail.com>
 * @since 2.0.0
 */
interface ComponentRegistrarInterface
{
    /**
     * Get list of registered Magento components
     *
     * Returns an array where key is fully-qualified component name and value is absolute path to component
     *
     * @param string $type
     * @return array
     * @since 2.0.0
     */
    public function getPaths($type);

    /**
     * Get path of a component if it is already registered
     *
     * @param string $type
     * @param string $componentName
     * @return null|string
     * @since 2.0.0
     */
    public function getPath($type, $componentName);
}
