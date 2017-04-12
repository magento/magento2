<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/** @var \Magento\SalesRule\Model\Rule $rule */
$rule = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(\Magento\SalesRule\Model\Rule::class);
$rule->setName(
    '#1'
)->setIsActive(
    1
)->setStopRulesProcessing(
    0
)->setIsAdvanced(
    1
)->setCouponType(
    Magento\SalesRule\Model\Rule::COUPON_TYPE_SPECIFIC
)->setUseAutoGeneration(
    0
)->setWebsiteIds(
    '1'
)->setCustomerGroupIds(
    '0'
)->setDiscountStep(0)
    ->save();

/** @var \Magento\SalesRule\Model\Rule $rule */
$rule = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(\Magento\SalesRule\Model\Rule::class);
$rule->setName(
    '#2'
)->setIsActive(
    1
)->setStopRulesProcessing(
    0
)->setIsAdvanced(
    1
)->setCouponType(
    Magento\SalesRule\Model\Rule::COUPON_TYPE_NO_COUPON
)->setUseAutoGeneration(
    0
)->setWebsiteIds(
    '1'
)->setCustomerGroupIds(
    '0'
)->setDiscountStep(0)
    ->save();

/** @var \Magento\SalesRule\Model\Rule $rule */
$rule = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(\Magento\SalesRule\Model\Rule::class);
$rule->setName(
    '#3'
)->setIsActive(
    1
)->setStopRulesProcessing(
    0
)->setIsAdvanced(
    1
)->setCouponType(
    Magento\SalesRule\Model\Rule::COUPON_TYPE_SPECIFIC
)->setUseAutoGeneration(
    1
)->setWebsiteIds(
    '1'
)->setCustomerGroupIds(
    '0'
)->setDiscountStep(0)
    ->save();

/** @var \Magento\SalesRule\Model\Rule $rule */
$rule = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(\Magento\SalesRule\Model\Rule::class);
$rule->setName(
    '#4'
)->setIsActive(
    1
)->setStopRulesProcessing(
    0
)->setIsAdvanced(
    1
)->setCouponType(
    Magento\SalesRule\Model\Rule::COUPON_TYPE_AUTO
)->setUseAutoGeneration(
    0
)->setWebsiteIds(
    '1'
)->setCustomerGroupIds(
    '0'
)->setDiscountStep(0)
    ->save();

/** @var \Magento\SalesRule\Model\Rule $rule */
$rule = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(\Magento\SalesRule\Model\Rule::class);
$rule->setName(
    '#5'
)->setIsActive(
    1
)->setStopRulesProcessing(
    0
)->setIsAdvanced(
    1
)->setCouponType(
    Magento\SalesRule\Model\Rule::COUPON_TYPE_NO_COUPON
)->setUseAutoGeneration(
    0
)->setWebsiteIds(
    '1'
)->setCustomerGroupIds(
    '0'
)->setDiscountStep(0)
    ->save();
