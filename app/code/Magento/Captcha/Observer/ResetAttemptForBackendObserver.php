<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Captcha\Observer;

use Magento\Captcha\Model\ResourceModel\LogFactory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Reset captcha attempts for Backend
 */
class ResetAttemptForBackendObserver implements ObserverInterface
{
    /**
     * @var LogFactory
     */
    public $resLogFactory;

    /**
     * @param LogFactory $resLogFactory
     */
    public function __construct(
        LogFactory $resLogFactory
    ) {
        $this->resLogFactory = $resLogFactory;
    }

    /**
     * Reset Attempts For Backend
     *
     * @param Observer $observer
     * @return void
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        $this->resLogFactory->create()->deleteUserAttempts($observer->getUser()->getUsername());
    }
}
