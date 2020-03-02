<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

/** @var \Magento\Framework\Registry $registry */
/** @var \Magento\SalesRule\Model\Rule $salesRule */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$registry = $objectManager->get(\Magento\Framework\Registry::class);
$salesRule = $objectManager->create(\Magento\SalesRule\Model\Rule::class);
$salesRuleId = $registry->registry('Magento/SalesRule/_files/cart_rule_50_percent_off_no_condition/salesRuleId');
if ($salesRuleId) {
    $salesRule->load($salesRuleId);
    if ($salesRule->getId()) {
        $salesRule->delete();
    }
}
