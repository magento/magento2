<?php
/**
 * SalesRule 10% discount coupon
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/** @var \Magento\SalesRule\Model\Rule $salesRule */
$salesRule = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\SalesRule\Model\Rule');
/** @var int $salesRuleId */
$salesRuleId = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Framework\Registry')
    ->registry('Magento/Checkout/_file/discount_10percent_generalusers');
$salesRule->load($salesRuleId);
$salesRule->delete();
