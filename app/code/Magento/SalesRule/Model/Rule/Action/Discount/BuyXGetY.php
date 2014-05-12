<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\SalesRule\Model\Rule\Action\Discount;

class BuyXGetY extends AbstractDiscount
{
    /**
     * @param \Magento\SalesRule\Model\Rule $rule
     * @param \Magento\Sales\Model\Quote\Item\AbstractItem $item
     * @param float $qty
     * @return \Magento\SalesRule\Model\Rule\Action\Discount\Data
     */
    public function calculate($rule, $item, $qty)
    {
        /** @var \Magento\SalesRule\Model\Rule\Action\Discount\Data $discountData */
        $discountData = $this->discountFactory->create();

        $itemPrice = $this->validator->getItemPrice($item);
        $baseItemPrice = $this->validator->getItemBasePrice($item);
        $itemOriginalPrice = $this->validator->getItemOriginalPrice($item);
        $baseItemOriginalPrice = $this->validator->getItemBaseOriginalPrice($item);

        $x = $rule->getDiscountStep();
        $y = $rule->getDiscountAmount();
        if (!$x || $y > $x) {
            return $discountData;
        }
        $buyAndDiscountQty = $x + $y;

        $fullRuleQtyPeriod = floor($qty / $buyAndDiscountQty);
        $freeQty = $qty - $fullRuleQtyPeriod * $buyAndDiscountQty;

        $discountQty = $fullRuleQtyPeriod * $y;
        if ($freeQty > $x) {
            $discountQty += $freeQty - $x;
        }

        $discountData->setAmount($discountQty * $itemPrice);
        $discountData->setBaseAmount($discountQty * $baseItemPrice);
        $discountData->setOriginalAmount($discountQty * $itemOriginalPrice);
        $discountData->setBaseOriginalAmount($discountQty * $baseItemOriginalPrice);

        return $discountData;
    }
}
