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
use Magento\Framework\Json\Helper\Data;
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
     * @var Data $helper
     */
    private $jsonHelper;

    /**
     * @param Context $context
     * @param NotificationStorage $notificationStorage
     * @param CustomerRepository $customerRepository
     * @param Session $customerSession
     * @param CookieManagerInterface $cookieManager
     * @param Data $jsonHelper
     */
    public function __construct(
        Context $context,
        NotificationStorage $notificationStorage,
        CustomerRepository $customerRepository,
        Session $customerSession,
        CookieManagerInterface $cookieManager,
        Data $jsonHelper
    ) {
        parent::__construct($context);
        $this->notificationStorage = $notificationStorage;
        $this->customerRepository = $customerRepository;
        $this->session = $customerSession;
        $this->cookieManager = $cookieManager;
        $this->jsonHelper = $jsonHelper;
    }

    /**
     * @return void
     */
    public function execute()
    {
        $customerData = $this->jsonHelper->jsonDecode($this->getRequest()->getContent());
        if (isset($customerData['customer_id']) && $this->notificationStorage->isExists(
                NotificationStorage::UPDATE_CUSTOMER_SESSION,
                $customerData['customer_id'])
        ) {
            $customer = $this->customerRepository->getById($customerData['customer_id']);
            $this->session->setCustomerData($customer);
            $this->session->setCustomerGroupId($customer->getGroupId());
            $this->session->regenerateId();
            $this->notificationStorage->remove(NotificationStorage::UPDATE_CUSTOMER_SESSION, $customerData['customer_id']);
        }
    }
}
