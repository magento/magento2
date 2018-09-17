<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Captcha\Observer;

use Magento\Framework\Event\ObserverInterface;

class ResetAttemptForBackendObserver implements ObserverInterface
{
    /*
      * @var \Magento\Captcha\Model\ResourceModel\LogFactory
      */
    public $resLogFactory;

    /**
     * @param \Magento\Captcha\Model\ResourceModel\LogFactory $resLogFactory
     */
    public function __construct(
        \Magento\Captcha\Model\ResourceModel\LogFactory $resLogFactory
    ) {
        $this->resLogFactory = $resLogFactory;
    }


    /**
     * Reset Attempts For Backend
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Magento\Captcha\Observer\ResetAttemptForBackendObserver
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        return $this->resLogFactory->create()->deleteUserAttempts($observer->getUser()->getUsername());
    }
}
