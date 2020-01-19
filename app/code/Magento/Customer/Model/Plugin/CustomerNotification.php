<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Model\Plugin;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Customer\NotificationStorage;
use Magento\Customer\Model\Session;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\Area;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\State;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;

class CustomerNotification
{
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
     * Initialize dependencies.
     *
     * @param Session $session
     * @param NotificationStorage $notificationStorage
     * @param State $state
     * @param CustomerRepositoryInterface $customerRepository
     * @param LoggerInterface $logger
     * @param RequestInterface|null $request
     */
    public function __construct(
        Session $session,
        NotificationStorage $notificationStorage,
        State $state,
        CustomerRepositoryInterface $customerRepository,
        LoggerInterface $logger,
        RequestInterface $request = null
    ) {
        $this->session = $session;
        $this->notificationStorage = $notificationStorage;
        $this->state = $state;
        $this->customerRepository = $customerRepository;
        $this->logger = $logger;
        $this->request = $request;
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
        $customerId = $this->session->getCustomerId();

        if ($this->isFrontendRequest() && $this->isPostRequest() && $this->isSessionUpdateRegisteredFor($customerId)) {
            try {
                $this->session->regenerateId();
                $customer = $this->customerRepository->getById($customerId);
                $this->session->setCustomerData($customer);
                $this->session->setCustomerGroupId($customer->getGroupId());
                $this->notificationStorage->remove(NotificationStorage::UPDATE_CUSTOMER_SESSION, $customer->getId());
            } catch (NoSuchEntityException $e) {
                $this->logger->error($e);
            }
        }
    }

    /**
     * Return the shared request.
     * If the request wasn't injected because of the backward compatible optional constructor dependency,
     * create a new request instance.
     *
     * @return RequestInterface
     */
    private function getRequest(): RequestInterface
    {
        if (null === $this->request) {
            $this->request = ObjectManager::getInstance()->get(RequestInterface::class);
        }
        return $this->request;
    }

    /**
     * Because RequestInterface has no isPost method the check is requied before calling it.
     *
     * @return bool
     */
    private function isPostRequest(): bool
    {
        $request = $this->getRequest();

        return method_exists($request, 'isPost') && $request->isPost();
    }

    /**
     * Check if the current application area is frontend.
     *
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function isFrontendRequest(): bool
    {
        return $this->state->getAreaCode() == Area::AREA_FRONTEND;
    }

    /**
     * True if the session for the given customer ID needs to be refreshed.
     *
     * @param int $customerId
     * @return bool
     */
    private function isSessionUpdateRegisteredFor($customerId): bool
    {
        return $this->notificationStorage->isExists(NotificationStorage::UPDATE_CUSTOMER_SESSION, $customerId);
    }
}
