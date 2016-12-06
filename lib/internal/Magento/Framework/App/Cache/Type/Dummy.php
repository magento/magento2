<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Cache\Type;

use Magento\Framework\App\CacheInterface;

/**
 * Dummy cache adapter
 *
 * for cases when need to disable interaction with cache
 * but no specific cache type is used
 */
class Dummy implements CacheInterface
{
    /**
     * Required by CacheInterface
     *
     * @return null
     */
    public function getFrontend()
    {
        return null;
    }

    /**
     * Pretend to load data from cache by id
     *
     * {@inheritdoc}
     */
    public function load($identifier)
    {
        return null;
    }

    /**
     * Pretend to save data
     *
     * {@inheritdoc}
     */
    public function save($data, $identifier, $tags = [], $lifeTime = null)
    {
        return false;
    }

    /**
     * Pretend to remove cached data by identifier
     *
     * {@inheritdoc}
     */
    public function remove($identifier)
    {
        return true;
    }

    /**
     * Pretend to clean cached data by specific tag
     *
     * {@inheritdoc}
     */
    public function clean($tags = [])
    {
        return true;
    }
}
