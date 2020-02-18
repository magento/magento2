<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Cache\Backend;

/**
 * Redis wrapper to suppress exceptions on save
 */
class Redis extends \Cm_Cache_Backend_Redis
{
    /**
     * The idea is that base implementation doesn't handle errors on save operations.
     * Which may occurs when Redis cannot evict keys, which is expected in some cases.
     *
     * @param string $data
     * @param string $id
     * @param array $tags
     * @param bool $specificLifetime
     * @return bool
     */
    public function save($data, $id, $tags = array(), $specificLifetime = false)
    {
        try {
            parent::save($data, $id, $tags, $specificLifetime);
        } catch (\Throwable $exception) {
            return false;
        }

        return true;
    }
}
