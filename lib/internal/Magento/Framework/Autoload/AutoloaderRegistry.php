<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Autoload;

use InvalidArgumentException;
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
    public static function registerAutoloader(AutoloaderInterface $newAutoloader): void
    {
        self::$autoloader = $newAutoloader;
    }

    /**
     * Returns the registered autoloader
     *
     * @throws InvalidArgumentException
     * @return AutoloaderInterface
     */
    public static function getAutoloader(): AutoloaderInterface
    {
        if (!self::$autoloader instanceof AutoloaderInterface) {
            throw new InvalidArgumentException('Autoloader is not registered, cannot be retrieved.');
        }

        return self::$autoloader;
    }
}
