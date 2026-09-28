<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */

/** @var Magento\Framework\Registry $registry */
$registry = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(\Magento\Framework\Registry::class);

/** @var Magento\SalesRule\Model\Rule $rule */
$rule = $registry->registry('cart_rule_free_shipping');
if ($rule) {
    $rule->delete();
}
