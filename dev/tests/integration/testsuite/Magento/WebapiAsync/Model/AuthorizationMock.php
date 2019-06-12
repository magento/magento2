<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\WebapiAsync\Model;

use Magento\Framework\Webapi\Authorization;

class AuthorizationMock extends Authorization
{
    /**
     * @param string[] $aclResources
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function isAllowed($aclResources)
    {
        return true;
    }
}
