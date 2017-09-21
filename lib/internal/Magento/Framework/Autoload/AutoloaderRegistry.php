<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Autoload;

use Magento\Framework\Autoload\AutoloaderInterface;

/**
 * Registry to store a static member autoloader
 */
class AutoloaderRegistry
{
    /**
     * @var AutoloaderInterface
     */
    protected static $autoloader;

    /**
     * Registers the given autoloader as a static member
     *
     * @param AutoloaderInterface $newAutoloader
     * @return void
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
