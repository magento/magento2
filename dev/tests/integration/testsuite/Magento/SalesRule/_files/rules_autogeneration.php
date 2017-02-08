<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/** @var \Magento\SalesRule\Model\Rule $rule */
$rule = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(\Magento\SalesRule\Model\Rule::class);
$rule->setName(
    'AUTO_RULE'
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
)->setDiscountStep(
    0
)->save();

/** @var $objectManager \Magento\TestFramework\ObjectManager */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var Magento\Framework\Registry $registry */
$registry = $objectManager->get(\Magento\Framework\Registry::class);
$registry->unregister('_fixture/Magento_SalesRule_Api_RuleRepository');
$registry->register('_fixture/Magento_SalesRule_Api_RuleRepository', $rule);
