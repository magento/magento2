<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Component;

/**
 * Provides ability to statically register libraries
 */
class ThemeRegistrar extends AbstractComponentRegistrar
{
    /**
     * This instance
     *
     * @var ThemeRegistrar
     */
    private static $instance;

    /**
     * returns an instance of theme registrar
     *
     * @return ThemeRegistrar
     */
    public static function getInstance()
    {
        if (static::$instance === null) {
            static::$instance = new ThemeRegistrar();
        }
        return static::$instance;
    }
}
