<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Autoload;

use Magento\Framework\Autoload\AutoloaderInterface;

/**
 * Registry to store a static member autoloader
 * @since 2.0.0
 */
class AutoloaderRegistry
{
    /**
     * @var AutoloaderInterface
     * @since 2.0.0
     */
    protected static $autoloader;

    /**
     * Registers the given autoloader as a static member
     *
     * @param AutoloaderInterface $newAutoloader
     * @return void
     * @since 2.0.0
     */
    public static function registerAutoloader(AutoloaderInterface $newAutoloader)
    {
        self::$autoloader = $newAutoloader;
    }

    /**
     * Returns the registered autoloader
     *
     * @throws \Exception
     * @return AutoloaderInterface
     * @since 2.0.0
     */
    public static function getAutoloader()
    {
        if (self::$autoloader !== null) {
            return self::$autoloader;
        } else {
            throw new \Exception('Autoloader is not registered, cannot be retrieved.');
        }
    }
}
