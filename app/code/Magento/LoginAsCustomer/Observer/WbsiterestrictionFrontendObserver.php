<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\LoginAsCustomer\Observer;

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
