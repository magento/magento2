<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
