<?php
/**
 * Authorization interface
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework;

/**
 * @api
 */
interface AuthorizationInterface
{
    /**
     * Check current user permission on resource and privilege
     *
     * @param   string $resource
     * @param   string $privilege
     * @return  boolean
     */
    public function isAllowed($resource, $privilege = null);
}
