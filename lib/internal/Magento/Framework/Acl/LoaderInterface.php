<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
 * @since 2.0.0
 */
interface LoaderInterface
{
    /**
     * Populate ACL with data from external storage
     *
     * @param \Magento\Framework\Acl $acl
     * @return void
     * @abstract
     * @since 2.0.0
     */
    public function populateAcl(\Magento\Framework\Acl $acl);
}
