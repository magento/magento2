<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Ui\Model;

/**
 * Class AuthorizationMock.
 */
class AuthorizationMock extends \Magento\Framework\Authorization
{
    /**
     * Check current user permission on resource and privilege.
     *
     * @param   string $resource
     * @param   string $privilege
     * @return  boolean
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function isAllowed($resource, $privilege = null)
    {
        return $resource !== 'Magento_Customer::manage';
    }
}
