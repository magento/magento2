<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Resolver;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\CustomerGraphQl\Model\Resolver\Customer\CustomerDataProvider;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\GraphQl\Query\ResolverInterface;

/**
 * Customers field resolver, used for GraphQL request processing.
 */
class CustomerUpdate implements ResolverInterface
{
    /**
     * @var CustomerDataProvider
     */
    private $customerResolver;

    /**
     * @var ValueFactory
     */
    private $valueFactory;

    /**
     * @var \Magento\Newsletter\Model\SubscriberFactory
     */
    protected $subscriberFactory;

    /**
     * @var CustomerRegistry
     */
    protected $customerRegistry;

    /**
     * @param CustomerDataProvider $customerResolver
     * @param ValueFactory $valueFactory
     */
    public function __construct(
        CustomerDataProvider $customerResolver,
        ValueFactory $valueFactory,
        \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory
    ) {
        $this->customerResolver = $customerResolver;
        $this->valueFactory = $valueFactory;
        $this->subscriberFactory = $subscriberFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) : Value {

        /** @var ContextInterface $context */
        if ((!$context->getUserId()) || $context->getUserType() == UserContextInterface::USER_TYPE_GUEST) {
            throw new GraphQlAuthorizationException(
                __(
                    'Current customer does not have access to the resource "%1"',
                    [\Magento\Customer\Model\Customer::ENTITY]
                )
            );
        }

        $this->customerResolver->updateAccountInformation($context->getUserId(), $args);

        if (isset($args['is_subscribed'])) {
            $this->customerResolver->manageSubscription($context->getUserId(), $args['is_subscribed']);
        }

        $data = $args;
        $result = function () use ($data) {
            return !empty($data) ? $data : [];
        };

        return $this->valueFactory->create($result);
    }
}
