<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\LoginAsCustomer\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * LoginAsCustomer observer
 */
class WbsiterestrictionFrontendObserver implements ObserverInterface
{
    /**
     * Disable website stub or private sales restriction for loginascustomer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $controller = $observer->getController();
        if ($controller->getRequest()->getModuleName() == 'loginascustomer') {
            $result = $observer->getResult();
            $result->setData('should_proceed', false);
        }
    }
}
