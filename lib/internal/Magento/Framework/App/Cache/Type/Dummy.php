<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\Cache\Type;

use \Magento\Framework\App\CacheInterface;

class Dummy implements CacheInterface
{
    /**
     * Required by CacheInterface
     *
     * @return Null
     */
    public function getFrontend()
    {
        return null;
    }

    /**
     * Pretend to load data from cache by id
     *
     * @param  string $identifier
     * @return null
     */
    public function load($identifier)
    {
        return null;
    }

    /**
     * Pretend to save data
     *
     * @param string $data
     * @param string $identifier
     * @param array $tags
     * @param int $lifeTime
     * @return bool
     */
    public function save($data, $identifier, $tags = [], $lifeTime = null)
    {
        return false;
    }

    /**
     * Pretend to remove cached data by identifier
     *
     * @param string $identifier
     * @return bool
     */
    public function remove($identifier)
    {
        return true;
    }

    /**
     * Pretend to clean cached data by specific tag
     *
     * @param array $tags
     * @return bool
     */
    public function clean($tags = [])
    {
        return true;
    }
}