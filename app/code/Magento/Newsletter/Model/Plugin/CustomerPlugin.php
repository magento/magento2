<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Newsletter\Model\Plugin;

use Magento\Customer\Api\CustomerRepositoryInterface as CustomerRepository;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Newsletter\Model\SubscriberFactory;

/**
 * Class \Magento\Newsletter\Model\Plugin\CustomerPlugin
 *
 */
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
        $this->subscriberFactory->create()->updateSubscription($result->getId());
        if ($result->getId() && $customer->getExtensionAttributes()) {
            if ($customer->getExtensionAttributes()->getIsSubscribed() === true) {
                $this->subscriberFactory->create()->subscribeCustomerById($result->getId());
            } elseif ($customer->getExtensionAttributes()->getIsSubscribed() === false) {
                $this->subscriberFactory->create()->unsubscribeCustomerById($result->getId());
            }
        }
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
     * @since 2.2.0
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
}
