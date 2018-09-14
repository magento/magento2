<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Resolver\Customer;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Store\Api\StoreResolverInterface;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Newsletter\Model\SubscriberFactory;

/**
 * Customer field data provider, used for GraphQL request processing.
 */
class CustomerDataUpdater
{
    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var \Magento\Newsletter\Model\SubscriberFactory
     */
    private $subscriberFactory;

    /**
     * @var StoreResolverInterface
     */
    private $storeResolver;

    /**
     * @var CustomerDataProvider
     */
    private $customerDataProvider;

    /**
     * CustomerDataUpdater constructor.
     *
     * @param CustomerRepositoryInterface $customerRepository
     * @param StoreResolverInterface $storeResolver
     * @param CustomerDataProvider $customerDataProvider
     * @param SubscriberFactory $subscriberFactory
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        StoreResolverInterface $storeResolver,
        CustomerDataProvider $customerDataProvider,
        SubscriberFactory $subscriberFactory
    ) {
        $this->customerRepository = $customerRepository;
        $this->storeResolver = $storeResolver;
        $this->customerDataProvider = $customerDataProvider;
        $this->subscriberFactory = $subscriberFactory;
    }

    /**
     * Manage customer subscription. Subscribe OR unsubscribe if required. Return new subscription status
     *
     * @param int $customerId
     * @param bool $newSubscriptionStatus
     * @return bool
     */
    public function manageSubscription(int $customerId, bool $newSubscriptionStatus): bool
    {
        $subscriber = $this->subscriberFactory->create()->loadByCustomerId($customerId);
        if ($newSubscriptionStatus === true && !$subscriber->isSubscribed()) {
            $this->subscriberFactory->create()->subscribeCustomerById($customerId);
        } elseif ($newSubscriptionStatus === false && $subscriber->isSubscribed()) {
            $this->subscriberFactory->create()->unsubscribeCustomerById($customerId);
        }
        /** Load subscribed again to get his new status after update subscription */
        $subscriber = $this->subscriberFactory->create()->loadByCustomerId($customerId);
        return $subscriber->isSubscribed();
    }

    /**
     * Update account information related to
     *
     * @param int $customerId
     * @param array $customerData
     * @return CustomerInterface
     * @throws GraphQlAuthorizationException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\State\InputMismatchException
     */
    public function updateAccountInformation(int $customerId, array $customerData): CustomerInterface
    {
        $customer = $this->customerRepository->getById($customerId);

        if (isset($customerData['email'])
            && $customer->getEmail() !== $customerData['email']
            && isset($customerData['password'])) {
            if ($this->customerDataProvider->isPasswordCorrect($customerData['password'], $customerId)) {
                $customer->setEmail($customerData['email']);
            } else {
                throw new GraphQlAuthorizationException(__('Invalid current user password.'));
            }
        }

        if (isset($customerData['firstname'])) {
            $customer->setFirstname($customerData['firstname']);
        }
        if (isset($customerData['lastname'])) {
            $customer->setLastname($customerData['lastname']);
        }

        $customer->setStoreId($this->storeResolver->getCurrentStoreId());
        $this->customerRepository->save($customer);

        return $customer;
    }
}
