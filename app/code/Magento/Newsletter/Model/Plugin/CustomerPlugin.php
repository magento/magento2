<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Newsletter\Model\Plugin;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Config\Share;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Newsletter\Model\Subscriber;
use Magento\Newsletter\Model\ResourceModel\Subscriber\CollectionFactory;
use Magento\Customer\Api\Data\CustomerExtensionInterface;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Newsletter\Model\SubscriptionManagerInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Api\SearchResults;
use Psr\Log\LoggerInterface;

/**
 * Newsletter Plugin for customer
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CustomerPlugin
{
    /**
     * @var ExtensionAttributesFactory
     */
    private $extensionFactory;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var SubscriptionManagerInterface
     */
    private $subscriptionManager;

    /**
     * @var Share
     */
    private $shareConfig;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var array
     */
    private $customerSubscriber = [];

    /**
     * @var SubscriberFactory
     */
    private $subscriberFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param SubscriberFactory $subscriberFactory
     * @param ExtensionAttributesFactory $extensionFactory
     * @param CollectionFactory $collectionFactory
     * @param SubscriptionManagerInterface $subscriptionManager
     * @param Share $shareConfig
     * @param StoreManagerInterface $storeManager
     * @param LoggerInterface $logger
     */
    public function __construct(
        SubscriberFactory $subscriberFactory,
        ExtensionAttributesFactory $extensionFactory,
        CollectionFactory $collectionFactory,
        SubscriptionManagerInterface $subscriptionManager,
        Share $shareConfig,
        StoreManagerInterface $storeManager,
        LoggerInterface $logger
    ) {
        $this->subscriberFactory = $subscriberFactory;
        $this->extensionFactory = $extensionFactory;
        $this->collectionFactory = $collectionFactory;
        $this->subscriptionManager = $subscriptionManager;
        $this->shareConfig = $shareConfig;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
    }

    /**
     * Plugin after create customer that updates any newsletter subscription that may have existed.
     *
     * If we have extension attribute (is_subscribed) we need to subscribe that customer
     *
     * @param CustomerRepositoryInterface $subject
     * @param CustomerInterface $result
     * @param CustomerInterface $customer
     * @return CustomerInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSave(
        CustomerRepositoryInterface $subject,
        CustomerInterface $result,
        CustomerInterface $customer
    ) {
        /** @var Subscriber $subscriber */
        $subscriber = $this->getSubscriber($result);
        $subscribeStatus = $this->getIsSubscribedFromExtensionAttributes($customer) ?? $subscriber->isSubscribed();
        $needToUpdate = $this->isSubscriptionChanged($result, $subscriber, $subscribeStatus);

        /**
         * If subscriber is waiting to confirm customer registration
         * and customer is already confirmed registration
         * than need to subscribe customer
         */
        if ($subscriber->getId()
            && (int)$subscriber->getStatus() === Subscriber::STATUS_UNCONFIRMED
            && empty($result->getConfirmation())
        ) {
            $needToUpdate = true;
            $subscribeStatus = true;
        }
        if ($needToUpdate) {
            $storeId = $this->getCurrentStoreId($result);
            $subscriber = $subscribeStatus
                ? $this->subscriptionManager->subscribeCustomer((int)$result->getId(), $storeId)
                : $this->subscriptionManager->unsubscribeCustomer((int)$result->getId(), $storeId);
            $this->customerSubscriber[(int)$result->getId()] = $subscriber;
        }
        $this->addIsSubscribedExtensionAttribute($result, $subscriber->isSubscribed());

        return $result;
    }

    /**
     * Get subscription status from extension customer attribute
     *
     * @param CustomerInterface $customer
     * @return bool|null
     */
    private function getIsSubscribedFromExtensionAttributes(CustomerInterface $customer): ?bool
    {
        $extensionAttributes = $customer->getExtensionAttributes();
        if ($extensionAttributes === null || $extensionAttributes->getIsSubscribed() === null) {
            return null;
        }

        return (bool)$extensionAttributes->getIsSubscribed();
    }

    /**
     * Get is customer subscription changed
     *
     * @param CustomerInterface $customer
     * @param Subscriber $subscriber
     * @param bool $newStatus
     * @return bool
     */
    private function isSubscriptionChanged(CustomerInterface $customer, Subscriber $subscriber, bool $newStatus): bool
    {
        if ($subscriber->isSubscribed() !== $newStatus) {
            return true;
        }

        if (!$subscriber->getId()) {
            return false;
        }

        /**
         * If customer has changed email or subscriber was loaded by email
         * than need to update customer subscription
         */
        return $customer->getEmail() !== $subscriber->getEmail() || (int)$subscriber->getCustomerId() === 0;
    }

    /**
     * Plugin around delete customer that updates any newsletter subscription that may have existed.
     *
     * @param CustomerRepositoryInterface $subject
     * @param callable $deleteCustomerById Function we are wrapping around
     * @param int $customerId Input to the function
     * @return bool
     */
    public function aroundDeleteById(
        CustomerRepositoryInterface $subject,
        callable $deleteCustomerById,
        $customerId
    ) {
        $customer = $subject->getById($customerId);
        $result = $deleteCustomerById($customerId);
        $this->deleteSubscriptionsAfterCustomerDelete($customer);

        return $result;
    }

    /**
     * Plugin after delete customer that updates any newsletter subscription that may have existed.
     *
     * @param CustomerRepositoryInterface $subject
     * @param bool $result
     * @param CustomerInterface $customer
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterDelete(CustomerRepositoryInterface $subject, $result, CustomerInterface $customer)
    {
        $this->deleteSubscriptionsAfterCustomerDelete($customer);
        return $result;
    }

    /**
     * Plugin after getById customer that obtains newsletter subscription status for given customer.
     *
     * @param CustomerRepositoryInterface $subject
     * @param CustomerInterface $customer
     * @return CustomerInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetById(CustomerRepositoryInterface $subject, CustomerInterface $customer)
    {
        $extensionAttributes = $customer->getExtensionAttributes();
        if ($extensionAttributes === null || $extensionAttributes->getIsSubscribed() === null) {
            $isSubscribed = $this->getSubscriber($customer)->isSubscribed();
            $this->addIsSubscribedExtensionAttribute($customer, $isSubscribed);
        }

        return $customer;
    }

    /**
     * Add subscription status to customer list
     *
     * @param CustomerRepositoryInterface $subject
     * @param SearchResults $searchResults
     * @return SearchResults
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetList(CustomerRepositoryInterface $subject, SearchResults $searchResults): SearchResults
    {
        foreach ($searchResults->getItems() as $customer) {
            /** @var CustomerExtensionInterface $extensionAttributes */
            $extensionAttributes = $customer->getExtensionAttributes();

            $isSubscribed = (int) $extensionAttributes->getIsSubscribed() === Subscriber::STATUS_SUBSCRIBED ?: false;
            $extensionAttributes->setIsSubscribed($isSubscribed);
        }

        return $searchResults;
    }

    /**
     * Set Is Subscribed extension attribute
     *
     * @param CustomerInterface $customer
     * @param bool $isSubscribed
     */
    private function addIsSubscribedExtensionAttribute(CustomerInterface $customer, bool $isSubscribed): void
    {
        $extensionAttributes = $customer->getExtensionAttributes();
        if ($extensionAttributes === null) {
            /** @var CustomerExtensionInterface $extensionAttributes */
            $extensionAttributes = $this->extensionFactory->create(CustomerInterface::class);
            $customer->setExtensionAttributes($extensionAttributes);
        }
        $extensionAttributes->setIsSubscribed($isSubscribed);
    }

    /**
     * Delete customer subscriptions
     *
     * @param CustomerInterface $customer
     * @return void
     */
    private function deleteSubscriptionsAfterCustomerDelete(CustomerInterface $customer): void
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('subscriber_email', $customer->getEmail());
        if ($this->shareConfig->isWebsiteScope()) {
            try {
                $storeIds = $this->storeManager->getWebsite($customer->getWebsiteId())->getStoreIds();
                $collection->addFieldToFilter('store_id', ['in' => $storeIds]);
            } catch (NoSuchEntityException $exception) {
                $this->logger->error($exception);
            }
        }
        /** @var Subscriber $subscriber */
        foreach ($collection as $subscriber) {
            $subscriber->delete();
        }
    }

    /**
     * Get Subscriber model by customer
     *
     * @param CustomerInterface $customer
     * @return Subscriber
     */
    private function getSubscriber(CustomerInterface $customer): Subscriber
    {
        $customerId = (int)$customer->getId();
        if (isset($this->customerSubscriber[$customerId])) {
            return $this->customerSubscriber[$customerId];
        }

        /** @var Subscriber $subscriber */
        $subscriber = $this->subscriberFactory->create();
        $websiteId = $this->getCurrentWebsiteId($customer);
        $subscriber->loadByCustomer((int)$customer->getId(), $websiteId);
        /**
         * If subscriber was't found by customer id then try to find subscriber by customer email.
         * It need when the customer is creating and he has already subscribed as guest by same email.
         */
        if (!$subscriber->getId()) {
            $subscriber->loadBySubscriberEmail((string)$customer->getEmail(), $websiteId);
        }
        $this->customerSubscriber[$customerId] = $subscriber;

        return $subscriber;
    }

    /**
     * Retrieve current website id
     *
     * @param CustomerInterface $customer
     * @return int
     */
    private function getCurrentWebsiteId(CustomerInterface $customer): int
    {
        return (int)$this->storeManager->getStore($this->getCurrentStoreId($customer))->getWebsiteId();
    }

    /**
     * Retrieve current store id
     *
     * @param CustomerInterface $customer
     * @return int
     */
    private function getCurrentStoreId(CustomerInterface $customer): int
    {
        $storeId = (int)$this->storeManager->getStore()->getId();
        if ($storeId === Store::DEFAULT_STORE_ID) {
            $storeId = (int)$customer->getStoreId();
        }

        return $storeId;
    }
}
