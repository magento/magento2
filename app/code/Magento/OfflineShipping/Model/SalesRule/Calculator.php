<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Shopping Cart Rule data model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\OfflineShipping\Model\SalesRule;

use Magento\SalesRule\Model\Validator;

/**
 * @api
 * @since 100.0.2
 */
class Calculator extends Validator
{
    /**
     * Quote item free shipping ability check
     * This process not affect information about applied rules, coupon code etc.
     * This information will be added during discount amounts processing
     *
     * @param   \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @return  \Magento\OfflineShipping\Model\SalesRule\Calculator
     */
    public function processFreeShipping(\Magento\Quote\Model\Quote\Item\AbstractItem $item)
    {
        $address = $item->getAddress();
        $item->setFreeShipping(false);

        foreach ($this->_getRules($address) as $rule) {
            /* @var $rule \Magento\SalesRule\Model\Rule */
            if (!$this->validatorUtility->canProcessRule($rule, $address)) {
                continue;
            }

            if (!$rule->getActions()->validate($item)) {
                continue;
            }

            switch ($rule->getSimpleFreeShipping()) {
                case Rule::FREE_SHIPPING_ITEM:
                    $item->setFreeShipping($rule->getDiscountQty() ? $rule->getDiscountQty() : true);
                    $item->setFreeShippingMethod($item->getAddress()->getShippingMethod());
                    break;

                case Rule::FREE_SHIPPING_ADDRESS:
                    $address->setFreeShipping(true);
                    break;
            }
            if ($rule->getStopRulesProcessing()) {
                break;
            }
        }
        return $this;
    }
}
