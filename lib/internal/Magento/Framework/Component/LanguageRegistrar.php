<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Component;

/**
 * Provides ability to statically register language
 */
class LanguageRegistrar extends AbstractComponentRegistrar
{
    /**
     * This instance
     *
     * @var LanguageRegistrar
     */
    private static $instance;

    /**
     * returns an instance of language registrar
     *
     * @return LanguageRegistrar
     */
    public static function getInstance()
    {
        if (static::$instance === null) {
            static::$instance = new LanguageRegistrar();
        }
        return static::$instance;
    }
}
