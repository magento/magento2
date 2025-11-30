<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */

use Magento\Framework\Registry;
use Magento\SalesRule\Model\Rule;
use Magento\TestFramework\Helper\Bootstrap;

/** @var Registry $registry */
$registry = Bootstrap::getObjectManager()->get(Registry::class);

/** @var Rule $rule */
$rule = $registry->registry('cart_rule_free_shipping_by_category');
if ($rule) {
    $rule->delete();
}
