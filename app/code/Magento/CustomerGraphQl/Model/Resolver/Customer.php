<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CustomerGraphQl\Model\Resolver;

use GraphQL\Type\Definition\ResolveInfo;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Data\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Resolver\ResolverInterface;
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
    public function resolve(Field $field, array $value = null, array $args = null, $context, ResolveInfo $info)
    {
        /** @var ResolverContextInterface $context */
        if ((!$context->getUserId()) || $context->getUserType() == 4) {
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
