<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

namespace Magento\Webapi\Model\Authorization;

use Magento\Authorization\Model\UserContextInterface;

/**
 * Guest user context
 */
class GuestUserContext implements UserContextInterface
{
    /**
     * {@inheritdoc}
     */
    public function getUserId()
    {
        return 0;
    }

    /**
     * {@inheritdoc}
     */
    public function getUserType()
    {
        return UserContextInterface::USER_TYPE_GUEST;
    }
}
