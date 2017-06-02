<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var Magento\Framework\Registry $registry */
$registry = $objectManager->get(\Magento\Framework\Registry::class);

/** @var \Magento\SalesRule\Model\Rule $salesRule */
$salesRule = $registry->registry('cart_rule_free_shipping');
if ($salesRule !== null) {
    $salesRule->delete();
    $registry->unregister('cart_rule_free_shipping');
}
