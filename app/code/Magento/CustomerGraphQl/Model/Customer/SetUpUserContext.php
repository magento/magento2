<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Customer;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Authorization\Model\UserContextInterface;

/**
 * Set up user context after creating new customer account
 */
class SetUpUserContext
{
    /**
     * Set up user context after creating new customer account
     *
     * @param ContextInterface $context
     * @param CustomerInterface $customer
     */
    public function execute(ContextInterface $context, CustomerInterface $customer)
    {
        $context->setUserId((int)$customer->getId());
        $context->setUserType(UserContextInterface::USER_TYPE_CUSTOMER);
    }
}
