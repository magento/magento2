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
use Magento\CustomerGraphQl\Model\Resolver\Customer\CustomerDataUpdater;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\GraphQl\Query\ResolverInterface;

/**
 * Customers Update resolver
 */
class CustomerUpdate implements ResolverInterface
{
    /**
     * @var CustomerDataProvider
     */
    private $customerResolver;

    /**
     * @var CustomerDataUpdater
     */
    private $customerUpdater;

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
     * CustomerUpdate constructor.
     *
     * @param CustomerDataProvider $customerResolver
     * @param CustomerDataUpdater $customerUpdater
     * @param ValueFactory $valueFactory
     * @param \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory
     */
    public function __construct(
        CustomerDataProvider $customerResolver,
        CustomerDataUpdater $customerUpdater,
        ValueFactory $valueFactory,
        \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory
    ) {
        $this->customerResolver = $customerResolver;
        $this->valueFactory = $valueFactory;
        $this->subscriberFactory = $subscriberFactory;
        $this->customerUpdater = $customerUpdater;
    }

    /**
     * @inheritdoc
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

        $customerId = $context->getUserId();
        $this->customerUpdater->updateAccountInformation($customerId, $args);
        $data = $this->customerResolver->getCustomerById($customerId);

        if (isset($args['is_subscribed'])) {
            $data['is_subscribed'] = $this->customerUpdater->manageSubscription($customerId, $args['is_subscribed']);
        }

        $result = function () use ($data) {
            return !empty($data) ? $data : [];
        };

        return $this->valueFactory->create($result);
    }
}
