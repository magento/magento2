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
 * @since 2.0.0
 */
interface AuthorizationInterface
{
    /**
     * Check current user permission on resource and privilege
     *
     * @param   string $resource
     * @param   string $privilege
     * @return  boolean
     * @since 2.0.0
     */
    public function isAllowed($resource, $privilege = null);
}
