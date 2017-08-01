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
 * @since 2.0.0
 */
interface ProviderInterface
{
    /**
     * Retrieve ACL resources
     *
     * @return array
     * @since 2.0.0
     */
    public function getAclResources();
}
