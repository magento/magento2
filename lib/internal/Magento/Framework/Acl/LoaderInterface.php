<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Acl;

/**
 * Access Control List loader
 *
 * All classes implementing this interface should have ability to populate ACL object
 * with data (roles/rules/resources) persisted in external storage.
 *
 * @api
 */
interface LoaderInterface
{
    /**
     * Populate ACL with data from external storage
     *
     * @param \Magento\Framework\Acl $acl
     * @return void
     * @abstract
     */
    public function populateAcl(\Magento\Framework\Acl $acl);
}
