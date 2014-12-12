<?php
/**
 * Access Control List loader. All classes implementing this interface should have ability to populate ACL object
 * with data (roles/rules/resources) persisted in external storage.
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\Acl;

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
