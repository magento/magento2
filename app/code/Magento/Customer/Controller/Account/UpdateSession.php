<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Account;

use Magento\Customer\Controller\AbstractAccount;
use Magento\Customer\Model\Customer\NotificationStorage;
use Magento\Customer\Model\ResourceModel\CustomerRepository;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session;
use Magento\Framework\Stdlib\CookieManagerInterface;

class UpdateSession extends AbstractAccount
{
    /**
     * @var NotificationStorage
     */
    private $notificationStorage;

    /**
     * @var CustomerRepository
     */
    private $customerRepository;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var CookieManagerInterface
     */
    private $cookieManager;

    /**
     * @param Context $context
     * @param NotificationStorage $notificationStorage
     * @param CustomerRepository $customerRepository
     * @param Session $customerSession
     */
    public function __construct(
        Context $context,
        NotificationStorage $notificationStorage,
        CustomerRepository $customerRepository,
        Session $customerSession,
        CookieManagerInterface $cookieManager
    ) {
        parent::__construct($context);
        $this->notificationStorage = $notificationStorage;
        $this->customerRepository = $customerRepository;
        $this->cookieManager = $cookieManager;
    }

    /**
     * @return void
     */
    public function execute()
    {
        $notification = $this->getRequest()->getPost('notification');
        $customerId = $this->getRequest()->getPost('customer_id');
        if ($notification && $customerId && $this->notificationStorage->isExists($notification, $customerId)) {
            $customer = $this->customerRepository->getById($customerId);
            $this->session->setCustomerData($customer);
            $this->cookieManager->deleteCookie(NotificationStorage::UPDATE_CUSTOMER_SESSION);
        }
    }
}
