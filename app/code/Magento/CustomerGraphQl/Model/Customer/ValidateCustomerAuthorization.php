<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Customer;

use Magento\CustomerGraphQl\Exception\GraphQlCustomerAuthorizationException;
use Magento\GraphQl\Model\Query\ContextInterface;

/**
 * Ensure the current GraphQL request is made by an authorized customer
 *
 * @api
 */
class ValidateCustomerAuthorization
{
    /**
     * Throw an exception if the current user is not an authorized customer
     *
     * @param ContextInterface $context
     * @return void
     * @throws GraphQlCustomerAuthorizationException
     */
    public function execute(ContextInterface $context): void
    {
        if (false === $context->getExtensionAttributes()->getIsCustomer()) {
            throw new GraphQlCustomerAuthorizationException();
        }
    }
}
