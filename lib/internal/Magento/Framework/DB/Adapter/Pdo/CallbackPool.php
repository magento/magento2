<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\DB\Adapter\Pdo;

class CallbackPool
{
    /**
     * Array of callbacks subscribed to commit transaction commit
     *
     * @var array
     */
    private static $commitCallbacks = [];

    /**
     * Add callback by hash key.
     *
     * @param string $hashKey
     * @param array $callback
     * @return void
     */
    public static function attach($hashKey, $callback)
    {
        self::$commitCallbacks[$hashKey][] = $callback;
    }

    /**
     * Remove callbacks by hash key.
     *
     * @param string $hashKey
     * @return void
     */
    public static function clear($hashKey)
    {
        self::$commitCallbacks[$hashKey] = [];
    }

    /**
     * Get callbacks by hash key.
     *
     * @param string $hashKey
     * @return array
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
