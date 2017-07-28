<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Captcha\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Class \Magento\Captcha\Observer\ResetAttemptForBackendObserver
 *
 * @since 2.0.0
 */
class ResetAttemptForBackendObserver implements ObserverInterface
{
    /*
      * @var \Magento\Captcha\Model\ResourceModel\LogFactory
     * @since 2.0.0
      */
    public $resLogFactory;

    /**
     * @param \Magento\Captcha\Model\ResourceModel\LogFactory $resLogFactory
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        return $this->resLogFactory->create()->deleteUserAttempts($observer->getUser()->getUsername());
    }
}
