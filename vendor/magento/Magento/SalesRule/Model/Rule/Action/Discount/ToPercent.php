<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\SalesRule\Model\Rule\Action\Discount;

class ToPercent extends ByPercent
{
    /**
     * @param \Magento\SalesRule\Model\Rule $rule
     * @param \Magento\Sales\Model\Quote\Item\AbstractItem $item
     * @param float $qty
     * @return Data
     */
    public function calculate($rule, $item, $qty)
    {
        $rulePercent = max(0, 100 - $rule->getDiscountAmount());
        $discountData = $this->_calculate($rule, $item, $qty, $rulePercent);

        return $discountData;
    }
}
