<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Model;

/**
 * Class CallbackPool
 * @since 2.1.0
 */
class CallbackPool
{
    /**
     * Array of callbacks subscribed to commit transaction commit
     *
     * @var array
     * @since 2.1.0
     */
    private static $commitCallbacks = [];

    /**
     * @param string $hashKey
     * @param array $callback
     * @return void
     * @since 2.1.0
     */
    public static function attach($hashKey, $callback)
    {
        self::$commitCallbacks[$hashKey][] = $callback;
    }

    /**
     * @param string $hashKey
     * @return void
     * @since 2.1.0
     */
    public static function clear($hashKey)
    {
        self::$commitCallbacks[$hashKey] = [];
    }

    /**
     * @param string $hashKey
     * @return array
     * @since 2.1.0
     */
    public static function get($hashKey)
    {
        if (!isset(self::$commitCallbacks[$hashKey])) {
            return [];
        }
        $callbacks = self::$commitCallbacks[$hashKey];
        self::$commitCallbacks[$hashKey] = [];
        return $callbacks;
    }
}
