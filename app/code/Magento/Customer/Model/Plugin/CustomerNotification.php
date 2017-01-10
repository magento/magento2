<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Plugin;

use Magento\Customer\Model\Customer\NotificationStorage;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\AbstractAction;
use Magento\Framework\App\Area;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\Http;
use Magento\Framework\App\State;
use Magento\Customer\Api\CustomerRepositoryInterface;

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
     * CustomerNotification constructor.
     * 
     * @param Session $session
     * @param NotificationStorage $notificationStorage
     * @param State $state
     */
    public function __construct(
        Session $session,
        NotificationStorage $notificationStorage,
        State $state,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->session = $session;
        $this->notificationStorage = $notificationStorage;
        $this->state = $state;
        $this->customerRepository = $customerRepository;
    }

    /**
     * @param AbstractAction $subject
     * @param RequestInterface $request
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeDispatch(AbstractAction $subject, RequestInterface $request)
    {
        if ($this->state->getAreaCode() == Area::AREA_FRONTEND && $request->isPost()
            && $this->notificationStorage->isExists(
                NotificationStorage::UPDATE_CUSTOMER_SESSION,
                $this->session->getCustomerId()
            )
        ) {
            $customer = $this->customerRepository->getById($this->session->getCustomerId());
            $this->session->setCustomerData($customer);
            $this->session->setCustomerGroupId($customer->getGroupId());
            $this->session->regenerateId();
            $this->notificationStorage->remove(NotificationStorage::UPDATE_CUSTOMER_SESSION, $customer->getId());
        }
    }
}
