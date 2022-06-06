<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Observer;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Customer\NotificationStorage;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;
use Magento\Framework\Session\StorageInterface;

/**
 * Observer to check if customer session needs to be regenerated
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class UpdateCustomerSessionObserver implements ObserverInterface
{
    /**
     * @var Session
     */
    private Session $session;

    /**
     * @var NotificationStorage
     */
    private NotificationStorage $notificationStorage;

    /**
     * @var State
     */
    private State $state;

    /**
     * @var CustomerRepositoryInterface
     */
    private CustomerRepositoryInterface $customerRepository;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**#@+
     * Array key for all active previous session ids.
     */
    private const PREVIOUS_ACTIVE_SESSIONS = 'previous_active_sessions';

    /**
     * @var StorageInterface|null
     */
    private StorageInterface $storage;

    /**
     *
     * Initialize dependencies.
     *
     * @param Session $session
     * @param NotificationStorage $notificationStorage
     * @param State $state
     * @param CustomerRepositoryInterface $customerRepository
     * @param LoggerInterface $logger
     * @param StorageInterface $storage
     */
    public function __construct(
        Session $session,
        NotificationStorage $notificationStorage,
        State $state,
        CustomerRepositoryInterface $customerRepository,
        LoggerInterface $logger,
        StorageInterface $storage

    ) {
        $this->session = $session;
        $this->notificationStorage = $notificationStorage;
        $this->state = $state;
        $this->customerRepository = $customerRepository;
        $this->logger = $logger;
        $this->storage = $storage;

    }

    /**
     * Update Customer Session Observer
     *
     * @param Observer $observer
     * @throws LocalizedException
     * @return void
     */
    public function execute(Observer $observer)
    {

        $customerId = (int)$observer->getCustomer()->getId();
        $isAreaFrontEnd = $this->state->getAreaCode() === Area::AREA_FRONTEND;

        if (!$isAreaFrontEnd || !$this->isSessionUpdateRegisteredFor($customerId)) {
            return;
        }
        try {
            $oldSessionId = session_id();
            $previousSessions = $this->storage->getData(self::PREVIOUS_ACTIVE_SESSIONS);

            if(empty($previousSessions)) {
                $previousSessions = [];
            }
            $previousSessions[] = $oldSessionId;
            $this->storage->setData(self::PREVIOUS_ACTIVE_SESSIONS, $previousSessions);
            $this->session->regenerateId();
            $customer = $this->customerRepository->getById($customerId);
            $this->session->setCustomerData($customer);
            $this->session->setCustomerGroupId($customer->getGroupId());
            $this->notificationStorage->remove(NotificationStorage::UPDATE_CUSTOMER_SESSION, $customer->getId());
        } catch (NoSuchEntityException $e) {
            $this->logger->error($e);
        }
    }

    /**
     * True if the session for the given customer ID needs to be refreshed.
     *
     * @param int $customerId
     * @return bool
     */
    private function isSessionUpdateRegisteredFor(int $customerId): bool
    {
        return (bool)$this->notificationStorage->isExists(
            NotificationStorage::UPDATE_CUSTOMER_SESSION,
            $customerId
        );
    }
}
