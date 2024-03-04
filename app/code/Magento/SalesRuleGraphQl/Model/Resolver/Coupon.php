<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 *
 * NOTICE: All information contained herein is, and remains
 * the property of Adobe and its suppliers, if any. The intellectual
 * and technical concepts contained herein are proprietary to Adobe
 * and its suppliers and are protected by all applicable intellectual
 * property laws, including trade secret and copyright laws.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained from
 * Adobe.
 */
declare(strict_types=1);

namespace Magento\SalesRuleGraphQl\Model\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\SalesRule\Api\Data\RuleDiscountInterface;
use Magento\SalesRule\Model\GetCoupons;
use Magento\SalesRule\Model\Quote\GetCouponCodes;

class Coupon implements ResolverInterface
{

    /**
     * @param GetCouponCodes $getCouponCodes
     * @param GetCoupons $getCoupons
     */
    public function __construct(
        private readonly GetCouponCodes $getCouponCodes,
        private readonly GetCoupons $getCoupons
    ) {
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!isset($value['discount_model'])) {
            throw new LocalizedException(__('"discount_model" value should be specified'));
        }
        if (!isset($value['quote_model'])) {
            throw new LocalizedException(__('"quote_model" value should be specified'));
        }
        /** @var RuleDiscountInterface $discount */
        $discount = $value['discount_model'];
        $quote = $value['quote_model'];

        $coupons = $this->getCoupons->execute($this->getCouponCodes->execute($quote));

        if (empty($coupons)) {
            return null;
        }

        foreach ($coupons as $coupon) {
            if ($coupon && $coupon->getRuleId() && $coupon->getRuleId() == $discount->getRuleID()) {
                return ['code' => $coupon->getCode()];
            }
        }

        return null;
    }
}
