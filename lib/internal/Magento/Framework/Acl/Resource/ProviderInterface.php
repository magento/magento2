<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Acl\Resource;

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
