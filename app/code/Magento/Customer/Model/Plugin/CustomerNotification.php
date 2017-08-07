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

/**
 * Class \Magento\Customer\Model\Plugin\CustomerNotification
 *
 * @since 2.1.0
 */
class CustomerNotification
{
    /**
     * @var Session
     * @since 2.1.0
     */
    private $session;

    /**
     * @var NotificationStorage
     * @since 2.1.0
     */
    private $notificationStorage;

    /**
     * @var CustomerRepositoryInterface
     * @since 2.1.0
     */
    private $customerRepository;

    /**
     * @var State
     * @since 2.1.0
     */
    private $state;

    /**
     * Initialize dependencies.
     *
     * @param Session $session
     * @param NotificationStorage $notificationStorage
     * @param State $state
     * @param CustomerRepositoryInterface $customerRepository
     * @since 2.1.0
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
     * @since 2.1.0
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
