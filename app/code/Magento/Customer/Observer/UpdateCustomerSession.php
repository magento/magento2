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
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Session\SaveHandlerInterface;

/**
 * Observer to check if customer session needs to be regenerated
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class UpdateCustomerSession implements ObserverInterface
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

    /**
     * @var SaveHandlerInterface|null
     */
    private $saveHandler;

    /**
     *
     * Initialize dependencies.
     *
     * @param Session $session
     * @param NotificationStorage $notificationStorage
     * @param State $state
     * @param CustomerRepositoryInterface $customerRepository
     * @param LoggerInterface $logger
     * @param SaveHandlerInterface|null $saveHandler
     */
    public function __construct(
        Session $session,
        NotificationStorage $notificationStorage,
        State $state,
        CustomerRepositoryInterface $customerRepository,
        LoggerInterface $logger,
        SaveHandlerInterface $saveHandler = null

    ) {
        $this->session = $session;
        $this->notificationStorage = $notificationStorage;
        $this->state = $state;
        $this->customerRepository = $customerRepository;
        $this->logger = $logger;
        $this->saveHandler = $saveHandler ?? ObjectManager::getInstance()->create(SaveHandlerInterface::class);

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
            $this->session->regenerateId();
            $customer = $this->customerRepository->getById($customerId);
            $this->session->setCustomerData($customer);
            $this->session->setCustomerGroupId($customer->getGroupId());
            $this->notificationStorage->remove(NotificationStorage::UPDATE_CUSTOMER_SESSION, $customer->getId());
            $this->destroyPreviousSession($oldSessionId);
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

    /**
     * Destroy previous session id
     *
     * @param string $sessionId
     * @return void
     */
    private function destroyPreviousSession(string $sessionId): void
    {
        $this->session->start();
        $this->saveHandler->destroy($sessionId);
    }
}
