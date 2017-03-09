<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Console;

/**
 * Locator for Console commands
 */
class CommandLocator
{
    /**
     * @var string[]
     */
    private static $commands = [];

    /**
     * @param string $commandListClass
     * @return void
     */
    public static function register($commandListClass)
    {
        self::$commands[] = $commandListClass;
    }

    /**
     * @return string[]
     */
    public static function getCommands()
    {
        return self::$commands;
    }
}
