<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Persistent\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Class UpdateCustomerCookies
 */
class UpdateCustomerCookiesObserver implements ObserverInterface
{
    /**
     * Persistent session
     *
     * @var \Magento\Persistent\Helper\Session
     */
    protected $_persistentSession;

    /**
     * Customer repository
     *
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * Constructor
     *
     * @param \Magento\Persistent\Helper\Session $persistentSession
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        \Magento\Persistent\Helper\Session $persistentSession,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
    ) {
        $this->_persistentSession = $persistentSession;
        $this->customerRepository = $customerRepository;
    }

    /**
     * Update customer id and customer group id if user is in persistent session
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->_persistentSession->isPersistent()) {
            return;
        }

        $customerCookies = $observer->getEvent()->getCustomerCookies();
        if ($customerCookies instanceof \Magento\Framework\DataObject) {
            $persistentCustomer = $this->customerRepository->getById(
                $this->_persistentSession->getSession()->getCustomerId()
            );
            $customerCookies->setCustomerId($persistentCustomer->getId());
            $customerCookies->setCustomerGroupId($persistentCustomer->getGroupId());
        }
    }
}
