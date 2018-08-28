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
use Magento\Store\Api\StoreResolverInterface;
use Magento\Framework\Encryption\EncryptorInterface as Encryptor;
use Magento\Customer\Model\CustomerRegistry;

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
     * @var StoreResolverInterface
     */
    private $storeResolver;

    /**
     * @var \Magento\Newsletter\Model\SubscriberFactory
     */
    protected $subscriberFactory;

    /**
     * @var CustomerRegistry
     */
    protected $customerRegistry;

    /**
     * @var Encryptor
     */
    protected $encryptor;

    /**
     * @param CustomerDataProvider $customerResolver
     * @param ValueFactory $valueFactory
     */
    public function __construct(
        CustomerDataProvider $customerResolver,
        ValueFactory $valueFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory,
        StoreResolverInterface $storeResolver,
        Encryptor $encryptor,
        CustomerRegistry $customerRegistry
    ) {
        $this->customerResolver = $customerResolver;
        $this->valueFactory = $valueFactory;
        $this->customerRepository = $customerRepository;
        $this->subscriberFactory = $subscriberFactory;
        $this->storeResolver = $storeResolver;
        $this->encryptor = $encryptor;
        $this->customerRegistry = $customerRegistry;
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

        $customer = $this->customerRepository->getById($context->getUserId());

        if (isset($args['email']) && $customer->getEmail() !== $args['email']) {
            $customerSecure = $this->customerRegistry->retrieveSecureData($context->getUserId());
            $hash = $customerSecure->getPasswordHash();
            if (!$this->encryptor->validateHash($args['password'], $hash)) {
                throw new GraphQlAuthorizationException(__('Invalid login or password.'));
            }
            $customer->setEmail($args['email']);
        }

        if (isset($args['firstname'])) {
            $customer->setFirstname($args['firstname']);
        }
        if (isset($args['lastname'])) {
            $customer->setLastname($args['lastname']);
        }

        $customer->setStoreId($this->storeResolver->getCurrentStoreId());
        $this->customerRepository->save($customer);

        if (isset($args['is_subscribed'])) {
            $checkSubscriber = $this->subscriberFactory->create()->loadByCustomerId($context->getUserId());
            if ($args['is_subscribed'] === true && !$checkSubscriber->isSubscribed()) {
                $this->subscriberFactory->create()->subscribeCustomerById($context->getUserId());
            } elseif ($args['is_subscribed'] === false && $checkSubscriber->isSubscribed()) {
                $this->subscriberFactory->create()->unsubscribeCustomerById($context->getUserId());
            }
        }

        $data = $args;
        $result = function () use ($data) {
            return !empty($data) ? $data : [];
        };

        return $this->valueFactory->create($result);
    }
}
