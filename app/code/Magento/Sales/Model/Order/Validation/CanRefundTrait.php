<?php

namespace Magento\Sales\Model\Order\Validation;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Config\ScopeConfigInterface;

trait CanRefundTrait
{
    /**
     * Return Zero GrandTotal availability.
     *
     * @return bool
     */
    private function isAllowZeroGrandTotal()
    {
        $scopeConfig = ObjectManager::getInstance()->get(ScopeConfigInterface::class);
        $isAllowed = $scopeConfig->getValue(
            'sales/zerograndtotal_creditmemo/allow_zero_grandtotal',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return $isAllowed;
    }
}
