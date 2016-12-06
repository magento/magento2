<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/** @var Magento\Framework\Registry $registry */
$registry = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(\Magento\Framework\Registry::class);

/** @var Magento\SalesRule\Model\Rule $rule */
$rule = $registry->registry('cart_rule_free_shipping');
if ($rule) {
    $rule->delete();
}
