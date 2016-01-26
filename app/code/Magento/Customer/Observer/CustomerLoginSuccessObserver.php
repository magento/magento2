<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Model\ResourceModel\LockoutManagement;

/**
 * Class CustomerLoginSuccessObserver
 */
class CustomerLoginSuccessObserver implements ObserverInterface
{
    /**
     * Lockout manager
     *
     * @var \Magento\Customer\Model\ResourceModel\LockoutManagement
     */
    protected $lockoutManager;

    /**
     * @param LockoutManagement $lockoutManager
     */
    public function __construct(
        LockoutManagement $lockoutManager
    ) {
        $this->lockoutManager = $lockoutManager;
    }

    /**
     * Unlock customer on success login attempt.
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Customer\Model\Customer $model */
        $model = $observer->getEvent()->getData('model');
        $this->lockoutManager->unlock($model->getId());
        return $this;
    }
}
