<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Component;

/**
 * Provides ability to statically register libraries
 */
class LibraryRegistrar extends AbstractComponentRegistrar
{
    /**
     * This instance
     *
     * @var LibraryRegistrar
     */
    private static $instance;

    /**
     * returns an instance of library registrar
     *
     * @return LibraryRegistrar
     */
    public static function getInstance()
    {
        if (static::$instance === null) {
            static::$instance = new LibraryRegistrar();
        }
        return static::$instance;
    }
}
