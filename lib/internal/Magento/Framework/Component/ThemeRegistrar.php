<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Component;

/**
 * Provides ability to statically register themes
 */
class ThemeRegistrar extends ComponentRegistrar
{
    /**
     * Paths to themes
     *
     * @var string[]
     */
    protected static $componentPaths = [];
}
