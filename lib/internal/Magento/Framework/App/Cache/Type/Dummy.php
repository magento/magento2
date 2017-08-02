<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Cache\Type;

use Magento\Framework\App\CacheInterface;

/**
 * Dummy cache adapter
 *
 * for cases when need to disable interaction with cache
 * but no specific cache type is used
 * @since 2.2.0
 */
class Dummy implements CacheInterface
{
    /**
     * Required by CacheInterface
     *
     * @return null
     * @since 2.2.0
     */
    public function getFrontend()
    {
        return null;
    }

    /**
     * Pretend to load data from cache by id
     *
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function load($identifier)
    {
        return null;
    }

    /**
     * Pretend to save data
     *
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function save($data, $identifier, $tags = [], $lifeTime = null)
    {
        return false;
    }

    /**
     * Pretend to remove cached data by identifier
     *
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function remove($identifier)
    {
        return true;
    }

    /**
     * Pretend to clean cached data by specific tag
     *
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function clean($tags = [])
    {
        return true;
    }
}
