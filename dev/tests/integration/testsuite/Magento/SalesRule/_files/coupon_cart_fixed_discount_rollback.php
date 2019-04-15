<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\TestFramework\Helper\Bootstrap;

/** @var Magento\Framework\Registry $registry */
$registry = Bootstrap::getObjectManager()->get(\Magento\Framework\Registry::class);

/** @var Magento\SalesRule\Model\Rule $rule */
$rule = $registry->registry('cart_rule_fixed_discount_coupon');
if ($rule) {
    $rule->delete();
}
