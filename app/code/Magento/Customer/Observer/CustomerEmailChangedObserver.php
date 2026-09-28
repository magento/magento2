<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Observer;

use Magento\Customer\Api\SessionCleanerInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\EmailNotificationInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Integration\Model\CustomerTokenService;

/**
 * Observer to logout customer when email is changed
 */
class CustomerEmailChangedObserver implements ObserverInterface
{
    /**
     * @var SessionCleanerInterface
     */
    private $sessionCleaner;

    /**
     * @var CustomerTokenService
     */
    private $customerTokenService;

    /**
     * @var EmailNotificationInterface
     */
    private $emailNotification;

    /**
     * @param SessionCleanerInterface $sessionCleaner
     * @param CustomerTokenService $customerTokenService
     * @param EmailNotificationInterface|null $emailNotification
     */
    public function __construct(
        SessionCleanerInterface $sessionCleaner,
        CustomerTokenService $customerTokenService,
        ?EmailNotificationInterface $emailNotification = null
    ) {
        $this->sessionCleaner = $sessionCleaner;
        $this->customerTokenService = $customerTokenService;
        $this->emailNotification = $emailNotification
            ?? ObjectManager::getInstance()->get(EmailNotificationInterface::class);
    }

    /**
     * Execute observer to revoke customer tokens and clear sessions
     *
     * @param Observer $observer
     * @return void
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        $customer = $observer->getEvent()->getData('customer');
        $originalCustomerEmail = $observer->getEvent()->getData('original_customer_email');
        $customerId = $customer->getId();
        if (!$customerId) {
            return;
        }
        try {
            $this->sessionCleaner->clearFor((int) $customerId);
            $this->customerTokenService->revokeCustomerAccessToken((int) $customerId);
            $this->sendEmailNotification($customer, $originalCustomerEmail);
        } catch (LocalizedException $e) {
            throw new LocalizedException(__('Something went wrong while logging out customer.'));
        }
    }

    /**
     * Sending email notification
     *
     * @param CustomerInterface $customer
     * @param string $originalCustomerEmail
     * @return void
     */
    private function sendEmailNotification($customer, $originalCustomerEmail) : void
    {
        $this->emailNotification->credentialsChanged(
            $customer,
            $originalCustomerEmail,
            false
        );
    }
}
