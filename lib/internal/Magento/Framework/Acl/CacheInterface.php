<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Acl;

/**
 * ACL object cache
 *
 * @api
 */
interface CacheInterface
{
    /**
     * Check whether ACL object is in cache
     *
     * @return bool
     */
    public function has();

    /**
     * Retrieve ACL object from cache
     *
     * @return \Magento\Framework\Acl
     */
    public function get();

    /**
     * Save ACL object to cache
     *
     * @param \Magento\Framework\Acl $acl
     * @return void
     */
    public function save(\Magento\Framework\Acl $acl);

    /**
     * Clear ACL instance cache
     *
     * @return void
     */
    public function clean();
}
