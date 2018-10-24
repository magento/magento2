<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Newsletter\Model\Plugin;

use Magento\Customer\Api\CustomerRepositoryInterface as CustomerRepository;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Newsletter\Model\ResourceModel\Subscriber;
use Magento\Customer\Api\Data\CustomerExtensionInterface;

class CustomerPlugin
{
    /**
     * Factory used for manipulating newsletter subscriptions
     *
     * @var SubscriberFactory
     */
    private $subscriberFactory;

    /**
     * @var ExtensionAttributesFactory
     */
    private $extensionFactory;

    /**
     * @var Subscriber
     */
    private $subscriberResource;

    /**
     * @var array
     */
    private $customerSubscriptionStatus = [];

    /**
     * Initialize dependencies.
     *
     * @param SubscriberFactory $subscriberFactory
     * @param ExtensionAttributesFactory $extensionFactory
     * @param Subscriber $subscriberResource
     */
    public function __construct(
        SubscriberFactory $subscriberFactory,
        ExtensionAttributesFactory $extensionFactory,
        Subscriber $subscriberResource
    ) {
        $this->subscriberFactory = $subscriberFactory;
        $this->extensionFactory = $extensionFactory;
        $this->subscriberResource = $subscriberResource;
    }

    /**
     * Plugin after create customer that updates any newsletter subscription that may have existed.
     *
     * If we have extension attribute (is_subscribed) we need to subscribe that customer
     *
     * @param CustomerRepository $subject
     * @param CustomerInterface $result
     * @param CustomerInterface $customer
     * @return CustomerInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSave(CustomerRepository $subject, CustomerInterface $result, CustomerInterface $customer)
    {
        $resultId = $result->getId();
        /** @var \Magento\Newsletter\Model\Subscriber $subscriber */
        $subscriber = $this->subscriberFactory->create();

        $subscriber->updateSubscription($resultId);
        // update the result only if the original customer instance had different value.
        $initialExtensionAttributes = $result->getExtensionAttributes();
        if ($initialExtensionAttributes === null) {
            /** @var CustomerExtensionInterface $initialExtensionAttributes */
            $initialExtensionAttributes = $this->extensionFactory->create(CustomerInterface::class);
            $result->setExtensionAttributes($initialExtensionAttributes);
        }

        $newExtensionAttributes = $customer->getExtensionAttributes();
        if ($newExtensionAttributes
            && $initialExtensionAttributes->getIsSubscribed() !== $newExtensionAttributes->getIsSubscribed()
        ) {
            if ($newExtensionAttributes->getIsSubscribed()) {
                $subscriber->subscribeCustomerById($resultId);
            } else {
                $subscriber->unsubscribeCustomerById($resultId);
            }
        }

        $isSubscribed = $subscriber->isSubscribed();
        $this->customerSubscriptionStatus[$resultId] = $isSubscribed;
        $initialExtensionAttributes->setIsSubscribed($isSubscribed);

        return $result;
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
     * Plugin after delete customer that updates any newsletter subscription that may have existed.
     *
     * @param CustomerRepository $subject
     * @param bool $result
     * @param CustomerInterface $customer
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterDelete(CustomerRepository $subject, $result, CustomerInterface $customer)
    {
        $subscriber = $this->subscriberFactory->create();
        $subscriber->loadByEmail($customer->getEmail());
        if ($subscriber->getId()) {
            $subscriber->delete();
        }
        return $result;
    }

    /**
     * Plugin after getById customer that obtains newsletter subscription status for given customer.
     *
     * @param CustomerRepository $subject
     * @param CustomerInterface $customer
     * @return CustomerInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetById(CustomerRepository $subject, CustomerInterface $customer)
    {
        $extensionAttributes = $customer->getExtensionAttributes();

        if ($extensionAttributes === null) {
            /** @var CustomerExtensionInterface $extensionAttributes */
            $extensionAttributes = $this->extensionFactory->create(CustomerInterface::class);
            $customer->setExtensionAttributes($extensionAttributes);
        }
        if ($extensionAttributes->getIsSubscribed() === null) {
            $isSubscribed = $this->isSubscribed($customer);
            $extensionAttributes->setIsSubscribed($isSubscribed);
        }

        return $customer;
    }

    /**
     * This method returns newsletters subscription status for given customer.
     *
     * @param CustomerInterface $customer
     * @return bool
     */
    private function isSubscribed(CustomerInterface $customer)
    {
        $customerId = $customer->getId();
        if (!isset($this->customerSubscriptionStatus[$customerId])) {
            $subscriber = $this->subscriberResource->loadByCustomerData($customer);
            $this->customerSubscriptionStatus[$customerId] = isset($subscriber['subscriber_status'])
                && $subscriber['subscriber_status'] == 1;
        }

        return $this->customerSubscriptionStatus[$customerId];
    }
}
