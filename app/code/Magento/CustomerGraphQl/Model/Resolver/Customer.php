<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CustomerGraphQl\Model\Resolver;

use Magento\Authorization\Model\UserContextInterface;
use Magento\CustomerGraphQl\Model\Resolver\Customer\CustomerDataProvider;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\GraphQl\Model\ResolverInterface;
use Magento\GraphQl\Model\ResolverContextInterface;

/**
 * Customers field resolver, used for GraphQL request processing.
 */
class Customer implements ResolverInterface
{
    /**
     * @var Customer\CustomerDataProvider
     */
    private $customerResolver;

    /**
     * @param CustomerDataProvider $customerResolver
     */
    public function __construct(
        CustomerDataProvider $customerResolver
    ) {
        $this->customerResolver = $customerResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(array $args, ResolverContextInterface $context)
    {
        if ((!$context->getUserId()) || $context->getUserType() == UserContextInterface::USER_TYPE_GUEST) {
            throw new GraphQlAuthorizationException(
                __(
                    'Current customer does not have access to the resource "%1"',
                    [\Magento\Customer\Model\Customer::ENTITY]
                )
            );
        }

        try {
            return $this->customerResolver->getCustomerById($context->getUserId());
        } catch (NoSuchEntityException $exception) {
            return new GraphQlNoSuchEntityException(__('Customer id %1 does not exist.', [$context->getUserId()]));
        }
    }
}
