<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Console;

/**
 * Locator for Console commands
 * @since 2.0.0
 */
class CommandLocator
{
    /**
     * @var string[]
     * @since 2.0.0
     */
    private static $commands = [];

    /**
     * @param string $commandListClass
     * @return void
     * @since 2.0.0
     */
    public static function register($commandListClass)
    {
        self::$commands[] = $commandListClass;
    }

    /**
     * @return string[]
     * @since 2.0.0
     */
    public static function getCommands()
    {
        return self::$commands;
    }
}
