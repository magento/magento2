<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CustomerGraphQl\Model\Resolver;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\GraphQl\Model\ResolverInterface;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
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
     * @param \Magento\CustomerGraphQl\Model\Resolver\Customer\CustomerDataProvider $customerResolver
     */
    public function __construct(
        \Magento\CustomerGraphQl\Model\Resolver\Customer\CustomerDataProvider $customerResolver
    ) {
        $this->customerResolver = $customerResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(array $args, ResolverContextInterface $context)
    {
        if ((!$context->getUserId()) || $context->getUserType() == 4) {
            throw new GraphQlInputException(
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
