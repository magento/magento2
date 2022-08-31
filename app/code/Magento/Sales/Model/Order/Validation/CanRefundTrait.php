<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Validation;

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
        $isAllowed = $this->objectManager->get(ScopeConfigInterface::class)->getValue(
            'sales/zerograndtotal_creditmemo/allow_zero_grandtotal',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return $isAllowed;
    }
}
