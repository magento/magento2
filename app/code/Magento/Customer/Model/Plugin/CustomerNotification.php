<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Plugin;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Customer\NotificationStorage;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\AbstractAction;
use Magento\Framework\App\Area;
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
     * Initialize dependencies.
     *
     * @param Session $session
     * @param NotificationStorage $notificationStorage
     * @param State $state
     * @param CustomerRepositoryInterface $customerRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        Session $session,
        NotificationStorage $notificationStorage,
        State $state,
        CustomerRepositoryInterface $customerRepository,
        LoggerInterface $logger
    ) {
        $this->session = $session;
        $this->notificationStorage = $notificationStorage;
        $this->state = $state;
        $this->customerRepository = $customerRepository;
        $this->logger = $logger;
    }

    /**
     * @param AbstractAction $subject
     * @param RequestInterface $request
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeDispatch(AbstractAction $subject, RequestInterface $request)
    {
        $customerId = $this->session->getCustomerId();

        if ($this->state->getAreaCode() == Area::AREA_FRONTEND && $request->isPost()
            && $this->notificationStorage->isExists(
                NotificationStorage::UPDATE_CUSTOMER_SESSION,
                $customerId
            )
        ) {
            try {
                $customer = $this->customerRepository->getById($customerId);
                $this->session->setCustomerData($customer);
                $this->session->setCustomerGroupId($customer->getGroupId());
                $this->session->regenerateId();
                $this->notificationStorage->remove(NotificationStorage::UPDATE_CUSTOMER_SESSION, $customer->getId());
            } catch (NoSuchEntityException $e) {
                $this->logger->error($e);
            }
        }
    }
}
