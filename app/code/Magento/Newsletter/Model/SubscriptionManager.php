<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Newsletter\Model;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\MailException;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Class to update newsletter subscription status
 */
class SubscriptionManager implements SubscriptionManagerInterface
{
    /**
     * @var SubscriberFactory
     */
    private $subscriberFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var AccountManagementInterface
     */
    private $customerAccountManagement;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var CustomerSubscriberCache
     */
    private $customerSubscriberCache;

    /**
     * @param SubscriberFactory $subscriberFactory
     * @param LoggerInterface $logger
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param AccountManagementInterface $customerAccountManagement
     * @param CustomerRepositoryInterface $customerRepository
     * @param CustomerSubscriberCache|null $customerSubscriberCache
     */
    public function __construct(
        SubscriberFactory $subscriberFactory,
        LoggerInterface $logger,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        AccountManagementInterface $customerAccountManagement,
        CustomerRepositoryInterface $customerRepository,
        CustomerSubscriberCache $customerSubscriberCache = null
    ) {
        $this->subscriberFactory = $subscriberFactory;
        $this->logger = $logger;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->customerAccountManagement = $customerAccountManagement;
        $this->customerRepository = $customerRepository;
        $this->customerSubscriberCache = $customerSubscriberCache
            ?? ObjectManager::getInstance()->get(CustomerSubscriberCache::class);
    }

    /**
     * @inheritdoc
     */
    public function subscribe(string $email, int $storeId): Subscriber
    {
        $websiteId = (int)$this->storeManager->getStore($storeId)->getWebsiteId();
        $subscriber = $this->subscriberFactory->create()->loadBySubscriberEmail($email, $websiteId);
        $currentStatus = (int)$subscriber->getStatus();
        if ($currentStatus === Subscriber::STATUS_SUBSCRIBED) {
            return $subscriber;
        }

        $status = $this->isConfirmNeed($storeId) ? Subscriber::STATUS_NOT_ACTIVE : Subscriber::STATUS_SUBSCRIBED;
        if (!$subscriber->getId()) {
            $subscriber->setSubscriberConfirmCode($subscriber->randomSequence());
            $subscriber->setSubscriberEmail($email);
        }
        $subscriber->setStatus($status)
            ->setStoreId($storeId)
            ->save();

        $this->sendEmailAfterChangeStatus($subscriber);

        return $subscriber;
    }

    /**
     * @inheritdoc
     */
    public function unsubscribe(string $email, int $storeId, string $confirmCode): Subscriber
    {
        $websiteId = (int)$this->storeManager->getStore($storeId)->getWebsiteId();
        /** @var Subscriber $subscriber */
        $subscriber = $this->subscriberFactory->create()->loadBySubscriberEmail($email, $websiteId);
        if (!$subscriber->getId()) {
            return $subscriber;
        }
        $subscriber->setCheckCode($confirmCode);
        $subscriber->unsubscribe();

        return $subscriber;
    }

    /**
     * @inheritdoc
     */
    public function subscribeCustomer(int $customerId, int $storeId): Subscriber
    {
        return $this->updateCustomerSubscription($customerId, $storeId, true);
    }

    /**
     * @inheritdoc
     */
    public function unsubscribeCustomer(int $customerId, int $storeId): Subscriber
    {
        return $this->updateCustomerSubscription($customerId, $storeId, false);
    }

    /**
     * Update customer newsletter subscription
     *
     * @param int $customerId
     * @param int $storeId
     * @param bool $status
     * @return Subscriber
     */
    private function updateCustomerSubscription(int $customerId, int $storeId, bool $status): Subscriber
    {
        $customer = $this->customerRepository->getById($customerId);
        $websiteId = (int)$this->storeManager->getStore($storeId)->getWebsiteId();
        $subscriber = $this->loadSubscriberByCustomer($customer, $websiteId);
        if (!$status && !$subscriber->getId()) {
            return $subscriber;
        }

        $newStatus = $this->getNewSubscriptionStatus($subscriber, $customer, $storeId, $status);
        $needToSendLetter = $this->saveSubscriber($subscriber, $customer, $storeId, $newStatus);
        if ($needToSendLetter) {
            $this->sendEmailAfterChangeStatus($subscriber);
        }

        return $subscriber;
    }

    /**
     * Load subscriber model by customer
     *
     * @param CustomerInterface $customer
     * @param int $websiteId
     * @return Subscriber
     */
    private function loadSubscriberByCustomer(CustomerInterface $customer, int $websiteId): Subscriber
    {
        $subscriber = $this->subscriberFactory->create();
        $subscriber->loadByCustomer((int)$customer->getId(), $websiteId);
        if (!$subscriber->getId()) {
            $subscriber->loadBySubscriberEmail((string)$customer->getEmail(), $websiteId);
        }

        return $subscriber;
    }

    /**
     * Save Subscriber model
     *
     * @param Subscriber $subscriber
     * @param CustomerInterface $customer
     * @param int $storeId
     * @param int $status
     * @return bool Need to send email
     */
    private function saveSubscriber(
        Subscriber $subscriber,
        CustomerInterface $customer,
        int $storeId,
        int $status
    ): bool {
        $statusChanged = (int)$subscriber->getStatus() !== $status;
        $emailChanged = $subscriber->getEmail() !== $customer->getEmail();
        if ($this->dontNeedToSaveSubscriber(
            $subscriber,
            $customer,
            $statusChanged,
            $storeId,
            $status,
            $emailChanged
        )) {
            return false;
        }

        if (!$subscriber->getId()) {
            $subscriber->setSubscriberConfirmCode($subscriber->randomSequence());
        }
        $customerId = (int)$customer->getId();
        $subscriber->setStatus($status)
            ->setStatusChanged($statusChanged)
            ->setCustomerId($customerId)
            ->setStoreId($storeId)
            ->setEmail($customer->getEmail())
            ->save();

        if ($statusChanged) {
            $this->customerSubscriberCache->setCustomerSubscriber($customerId, null);
            return true;
        }

        /**
         * If the subscriber is waiting to confirm from the customer
         * or customer changed the email
         * than need to send confirmation letter to the new email
         */
        return $status === Subscriber::STATUS_NOT_ACTIVE || $emailChanged;
    }

    /**
     *  Don't need to save subscriber model
     *
     * @param Subscriber $subscriber
     * @param CustomerInterface $customer
     * @param bool $statusChanged
     * @param int $storeId
     * @param int $status
     * @param bool $emailChanged
     * @return bool
     */
    private function dontNeedToSaveSubscriber(
        Subscriber $subscriber,
        CustomerInterface $customer,
        bool $statusChanged,
        int $storeId,
        int $status,
        bool $emailChanged
    ): bool {
        return $subscriber->getId()
            && !$statusChanged
            && (int)$subscriber->getCustomerId() === (int)$customer->getId()
            && (int)$subscriber->getStoreId() === $storeId
            && !$emailChanged
            && $status !== Subscriber::STATUS_NOT_ACTIVE;
    }

    /**
     * Get new subscription status
     *
     * @param Subscriber $subscriber
     * @param CustomerInterface $customer
     * @param int $storeId
     * @param bool $subscribe
     * @return int
     */
    private function getNewSubscriptionStatus(
        Subscriber $subscriber,
        CustomerInterface $customer,
        int $storeId,
        bool $subscribe
    ): int {
        $currentStatus = (int)$subscriber->getStatus();
        // If the current status is already as needed then return them
        if (($subscribe && $currentStatus === Subscriber::STATUS_SUBSCRIBED)
            || (!$subscribe && $currentStatus === Subscriber::STATUS_UNSUBSCRIBED)
        ) {
            return $currentStatus;
        }

        $status = $currentStatus;
        if ($subscribe) {
            $customerConfirmStatus = $this->customerAccountManagement->getConfirmationStatus($customer->getId());
            if ($customerConfirmStatus === AccountManagementInterface::ACCOUNT_CONFIRMATION_REQUIRED) {
                $status = Subscriber::STATUS_UNCONFIRMED;
            } elseif ($this->isConfirmNeed($storeId)) {
                $status = Subscriber::STATUS_NOT_ACTIVE;
            } else {
                $status = Subscriber::STATUS_SUBSCRIBED;
            }
        } elseif ($currentStatus === Subscriber::STATUS_SUBSCRIBED) {
            $status = Subscriber::STATUS_UNSUBSCRIBED;
        }

        return $status;
    }

    /**
     * Sends out email to customer after change subscription status
     *
     * @param Subscriber $subscriber
     * @return void
     */
    private function sendEmailAfterChangeStatus(Subscriber $subscriber): void
    {
        $status = (int)$subscriber->getStatus();
        if ($status === Subscriber::STATUS_UNCONFIRMED) {
            return;
        }

        try {
            switch ($status) {
                case Subscriber::STATUS_UNSUBSCRIBED:
                    $subscriber->sendUnsubscriptionEmail();
                    break;
                case Subscriber::STATUS_SUBSCRIBED:
                    $subscriber->sendConfirmationSuccessEmail();
                    break;
                case Subscriber::STATUS_NOT_ACTIVE:
                    $subscriber->sendConfirmationRequestEmail();
                    break;
            }
        } catch (MailException $e) {
            // If we are not able to send a new account email, this should be ignored
            $this->logger->critical($e);
        }
    }

    /**
     * Is need to confirm subscription
     *
     * @param int $storeId
     * @return bool
     */
    private function isConfirmNeed(int $storeId): bool
    {
        return (bool)$this->scopeConfig->isSetFlag(
            Subscriber::XML_PATH_CONFIRMATION_FLAG,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
