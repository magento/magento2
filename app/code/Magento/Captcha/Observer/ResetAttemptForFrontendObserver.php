<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Captcha\Observer;

use Magento\Captcha\Model\ResourceModel\Log;
use Magento\Captcha\Model\ResourceModel\LogFactory;
use Magento\Customer\Model\Customer;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Reset captcha attempts for Frontend
 */
class ResetAttemptForFrontendObserver implements ObserverInterface
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
     * Reset Attempts For Frontend
     *
     * @param Observer $observer
     * @return Log
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        /** @var Customer $model */
        $model = $observer->getModel();

        return $this->resLogFactory->create()->deleteUserAttempts($model->getEmail());
    }
}
