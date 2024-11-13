<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Model\Plugin;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Customer\NotificationStorage;
use Magento\Customer\Model\Session;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\Area;
use Magento\Framework\App\HttpRequestInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\State;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Session\StorageInterface;
use Psr\Log\LoggerInterface;

/**
 * Refresh the Customer session if `UpdateSession` notification registered
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CustomerNotification
{
    /**
     * Array key for all active previous session ids.
     */
    private const PREVIOUS_ACTIVE_SESSIONS = 'previous_active_sessions';

    /**
     * @var Session
     */
    private $session;

    /**
     * @var NotificationStorage
     */
    private $notificationStorage;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var State
     */
    private $state;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var RequestInterface|\Magento\Framework\App\Request\Http
     */
    private $request;

    /**
     * @var StorageInterface
     */
    private StorageInterface $storage;

    /**
     * Initialize dependencies.
     *
     * @param Session $session
     * @param NotificationStorage $notificationStorage
     * @param State $state
     * @param CustomerRepositoryInterface $customerRepository
     * @param LoggerInterface $logger
     * @param RequestInterface $request
     * @param StorageInterface|null $storage
     */
    public function __construct(
        Session $session,
        NotificationStorage $notificationStorage,
        State $state,
        CustomerRepositoryInterface $customerRepository,
        LoggerInterface $logger,
        RequestInterface $request,
        StorageInterface $storage = null
    ) {
        $this->session = $session;
        $this->notificationStorage = $notificationStorage;
        $this->state = $state;
        $this->customerRepository = $customerRepository;
        $this->logger = $logger;
        $this->request = $request;
        $this->storage = $storage ?? ObjectManager::getInstance()->get(StorageInterface::class);
    }

    /**
     * Refresh the customer session on frontend post requests if an update session notification is registered.
     *
     * @param ActionInterface $subject
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeExecute(ActionInterface $subject)
    {
        $customerId = (int)$this->session->getCustomerId();

        if (!$this->isFrontendRequest()
            || !$this->isPostRequest()
            || $this->isLogoutRequest()
            || !$this->isSessionUpdateRegisteredFor($customerId)) {
            return;
        }

        try {
            $oldSessionId = $this->session->getSessionId();
            $previousSessions = $this->storage->getData(self::PREVIOUS_ACTIVE_SESSIONS);

            if (empty($previousSessions)) {
                $previousSessions = [];
            }
            $previousSessions[] = $oldSessionId;
            $this->storage->setData(self::PREVIOUS_ACTIVE_SESSIONS, $previousSessions);
            $this->session->regenerateId();
            $customer = $this->customerRepository->getById($customerId);
            $this->session->setCustomerData($customer);
            $this->session->setCustomerGroupId($customer->getGroupId());
            $this->notificationStorage->remove(
                NotificationStorage::UPDATE_CUSTOMER_SESSION,
                $customer->getId()
            );
        } catch (NoSuchEntityException $e) {
            $this->logger->error($e);
        }
    }

    /**
     * Because RequestInterface has no isPost method the check is requied before calling it.
     *
     * @return bool
     */
    private function isPostRequest(): bool
    {
        return $this->request instanceof HttpRequestInterface && $this->request->isPost();
    }

    /**
     * Checks if the current request is a logout request.
     *
     * @return bool
     */
    private function isLogoutRequest(): bool
    {
        return $this->request->getRouteName() === 'customer'
            && $this->request->getControllerName() === 'account'
            && $this->request->getActionName() === 'logout';
    }

    /**
     * Check if the current application area is frontend.
     *
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function isFrontendRequest(): bool
    {
        return $this->state->getAreaCode() === Area::AREA_FRONTEND;
    }

    /**
     * True if the session for the given customer ID needs to be refreshed.
     *
     * @param int $customerId
     * @return bool
     */
    private function isSessionUpdateRegisteredFor(int $customerId): bool
    {
        return (bool)$this->notificationStorage->isExists(NotificationStorage::UPDATE_CUSTOMER_SESSION, $customerId);
    }
}
