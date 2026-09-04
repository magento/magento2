<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
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
