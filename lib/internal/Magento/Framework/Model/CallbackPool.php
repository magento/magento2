<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Model;

use Magento\Framework\DB\Adapter\Pdo\CallbackPool as PdoCallbackPool;

/**
 * @deprecated please use Magento\Framework\DB\Adapter\Pdo\CallbackPool.
 */
class CallbackPool
{
    /**
     * Add callback by hash key.
     *
     * @param string $hashKey
     * @param array $callback
     * @return void
     */
    public static function attach($hashKey, $callback)
    {
        PdoCallbackPool::attach($hashKey, $callback);
    }

    /**
     * Remove callbacks by hash key.
     *
     * @param string $hashKey
     * @return void
     */
    public static function clear($hashKey)
    {
        PdoCallbackPool::clear($hashKey);
    }

    /**
     * Get callbacks by hash key.
     *
     * @param string $hashKey
     * @return array
     */
    public static function get($hashKey)
    {
        return PdoCallbackPool::get($hashKey);
    }
}
