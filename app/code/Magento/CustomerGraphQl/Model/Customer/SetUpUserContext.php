<?php

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
     * @param ContextInterface $context
     * @param CustomerInterface $customer
     */
    public function execute(ContextInterface $context, CustomerInterface $customer)
    {
        $context->setUserId((int)$customer->getId());
        $context->setUserType(UserContextInterface::USER_TYPE_CUSTOMER);
    }
}
