<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
use Magento\Framework\ObjectManagerInterface;
use Magento\SalesRule\Api\CouponRepositoryInterface;
use Magento\SalesRule\Api\Data\CouponInterface;
use Magento\SalesRule\Api\Data\RuleInterface;
use Magento\SalesRule\Api\RuleRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;

/** @var ObjectManagerInterface $objectManager */
$objectManager = Bootstrap::getObjectManager();

/** @var RuleInterface $rule */
$rule = $objectManager->create(RuleInterface::class);
$rule->setName('10$ discount')
    ->setIsAdvanced(true)
    ->setStopRulesProcessing(false)
    ->setDiscountQty(10)
    ->setCustomerGroupIds([0])
    ->setWebsiteIds([1])
    ->setCouponType(RuleInterface::COUPON_TYPE_SPECIFIC_COUPON)
    ->setSimpleAction(RuleInterface::DISCOUNT_ACTION_FIXED_AMOUNT_FOR_CART)
    ->setDiscountAmount(10)
    ->setIsActive(true);

/** @var RuleRepositoryInterface $ruleRepository */
$ruleRepository = $objectManager->get(RuleRepositoryInterface::class);
$rule = $ruleRepository->save($rule);

/** @var CouponInterface $coupon */
$coupon = $objectManager->create(CouponInterface::class);
$coupon->setCode('10_discount')
    ->setRuleId($rule->getRuleId());

/** @var CouponRepositoryInterface $couponRepository */
$couponRepository = $objectManager->get(CouponRepositoryInterface::class);
$coupon = $couponRepository->save($coupon);
