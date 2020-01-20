<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomer\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * LoginAsCustomer observer
 */
class WbsiterestrictionFrontendObserver implements ObserverInterface
{
    /**
     * Disable website stub or private sales restriction for loginascustomer
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer): void
    {
        $controller = $observer->getController();
        if ($controller->getRequest()->getModuleName() == 'loginascustomer') {
            $result = $observer->getResult();
            $result->setData('should_proceed', false);
        }
    }
}
