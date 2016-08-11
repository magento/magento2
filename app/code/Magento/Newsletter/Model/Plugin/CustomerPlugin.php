<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Newsletter\Model\Plugin;

use Magento\Customer\Api\CustomerRepositoryInterface as CustomerRepository;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Newsletter\Model\SubscriberFactory;

class CustomerPlugin
{
    /**
     * Factory used for manipulating newsletter subscriptions
     *
     * @var SubscriberFactory
     */
    private $subscriberFactory;

    /**
     * Initialize dependencies.
     *
     * @param SubscriberFactory $subscriberFactory
     */
    public function __construct(SubscriberFactory $subscriberFactory)
    {
        $this->subscriberFactory = $subscriberFactory;
    }

    /**
     * Plugin after create customer that updates any newsletter subscription that may have existed.
     *
     * @param CustomerRepository $subject
     * @param CustomerInterface $customer
     * @return CustomerInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSave(CustomerRepository $subject, CustomerInterface $customer)
    {
        $this->subscriberFactory->create()->updateSubscription($customer->getId());
        return $customer;
    }

    /**
     * Plugin around customer repository save. If we have extension attribute (is_subscribed) we need to subscribe that customer
     *
     * @param CustomerRepository $subject
     * @param \Closure $proceed
     * @param CustomerInterface $customer
     * @param null $passwordHash
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundSave(
        CustomerRepository $subject,
        \Closure $proceed,
        CustomerInterface $customer,
        $passwordHash = null
    ) {
        /** @var CustomerInterface $savedCustomer */
        $savedCustomer = $proceed($customer, $passwordHash);

        if ($savedCustomer->getId() && $customer->getExtensionAttributes()) {
            if ($customer->getExtensionAttributes()->getIsSubscribed() === true) {
                $this->subscriberFactory->create()->subscribeCustomerById($savedCustomer->getId());
            } elseif ($customer->getExtensionAttributes()->getIsSubscribed() === false) {
                $this->subscriberFactory->create()->unsubscribeCustomerById($savedCustomer->getId());
            }
        }

        return $savedCustomer;
    }
    
    /**
     * Plugin around delete customer that updates any newsletter subscription that may have existed.
     *
     * @param CustomerRepository $subject
     * @param callable $deleteCustomerById Function we are wrapping around
     * @param int $customerId Input to the function
     * @return bool
     */
    public function aroundDeleteById(
        CustomerRepository $subject,
        callable $deleteCustomerById,
        $customerId
    ) {
        $customer = $subject->getById($customerId);
        $result = $deleteCustomerById($customerId);
        /** @var \Magento\Newsletter\Model\Subscriber $subscriber */
        $subscriber = $this->subscriberFactory->create();
        $subscriber->loadByEmail($customer->getEmail());
        if ($subscriber->getId()) {
            $subscriber->delete();
        }
        return $result;
    }

    /**
     * Plugin around delete customer that updates any newsletter subscription that may have existed.
     *
     * @param CustomerRepository $subject
     * @param callable $deleteCustomer Function we are wrapping around
     * @param CustomerInterface $customer Input to the function
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundDelete(
        CustomerRepository $subject,
        callable $deleteCustomer,
        $customer
    ) {
        $result = $deleteCustomer($customer);
        /** @var \Magento\Newsletter\Model\Subscriber $subscriber */
        $subscriber = $this->subscriberFactory->create();
        $subscriber->loadByEmail($customer->getEmail());
        if ($subscriber->getId()) {
            $subscriber->delete();
        }
        return $result;
    }
}
