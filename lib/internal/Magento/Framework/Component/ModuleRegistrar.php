<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Component;

/**
 * Provides ability to statically register modules
 *
 * @author Josh Di Fabio <joshdifabio@gmail.com>
 */
class ModuleRegistrar extends ComponentRegistrar
{
    /**
     * Paths to modules
     *
     * @var string[]
     */
    protected static $componentPaths = [];
}
