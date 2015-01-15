<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Plugin\Element\Html\Link;

use Magento\Framework\View\Element\Html\Link;

class Current
{
    public function beforeIsCurrent(\Magento\Framework\View\Element\Html\Link\Current $subject)
    {
        if (
            $this->isSalesOrderViewPage($subject)
            && 'customer-account-navigation-orders-link' == $subject->getNameInLayout()
        ) {
            $subject->setData('current', true);
        }
    }

    /**
     * @return bool
     */
    public function isSalesOrderViewPage(\Magento\Framework\View\Element\Html\Link\Current $subject)
    {
        $request = $subject->getRequest();
        if (
            $request->getModuleName() == 'sales'
            && $request->getControllerName() == 'order'
            && $request->getActionName() == 'view'
        ) {
            return true;
        }
        return false;
    }
}