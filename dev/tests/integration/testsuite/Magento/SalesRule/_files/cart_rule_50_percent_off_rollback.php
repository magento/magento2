<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/** @var Magento\Framework\Registry $registry */
$registry = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(\Magento\Framework\Registry::class);

/** @var Magento\SalesRule\Model\Rule $rule */
$rule = $registry->registry('cart_rule_50_percent_off');
if ($rule) {
    $rule->delete();
}
