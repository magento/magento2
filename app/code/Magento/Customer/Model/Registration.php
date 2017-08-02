<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model;

/**
 * @api
 * @since 2.0.0
 */
class Registration
{
    /**
     * Check whether customers registration is allowed
     *
     * @return bool
     * @since 2.0.0
     */
    public function isAllowed()
    {
        return true;
    }
}
