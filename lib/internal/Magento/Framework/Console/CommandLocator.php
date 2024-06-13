<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
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
