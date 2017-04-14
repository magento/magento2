<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model;

class Registration
{
    /**
     * Check whether customers registration is allowed
     *
     * @return bool
     */
    public function isAllowed()
    {
        return true;
    }
}
