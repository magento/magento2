<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Component;

/**
 * Component Registrar Interface
 *
 * @api
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
     */
    public function getPaths($type);

    /**
     * Get path of a component if it is already registered
     *
     * @param string $type
     * @param string $componentName
     * @return null|string
     */
    public function getPath($type, $componentName);
}
