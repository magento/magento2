<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Ui\Model;

/**
<<<<<<< HEAD
 * Check current user permission on resource and privilege.
=======
 * Class AuthorizationMock
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
 */
class AuthorizationMock extends \Magento\Framework\Authorization
{
    /**
     * Check current user permission on resource and privilege
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
