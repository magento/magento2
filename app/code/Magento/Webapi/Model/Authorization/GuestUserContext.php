<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Webapi\Model\Authorization;

use Magento\Authorization\Model\UserContextInterface;

/**
 * Guest user context
 * @since 2.0.0
 */
class GuestUserContext implements UserContextInterface
{
    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getUserId()
    {
        return 0;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getUserType()
    {
        return UserContextInterface::USER_TYPE_GUEST;
    }
}
