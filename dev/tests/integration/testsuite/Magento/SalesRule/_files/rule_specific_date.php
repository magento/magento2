<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/** @var \Magento\SalesRule\Model\Rule $rule */
$rule = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\SalesRule\Model\Rule');
$rule->setName(
    '#1'
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
)->setFromDate(
    date('Y-m-d')
)->setToDate(
    date('Y-m-d')
)->save();
