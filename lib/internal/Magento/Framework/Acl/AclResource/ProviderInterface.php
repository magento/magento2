<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Acl\AclResource;

/**
 * Acl resources provider interface
 *
 * @api
 */
interface ProviderInterface
{
    /**
     * Retrieve ACL resources
     *
     * @return array
     */
    public function getAclResources();
}
