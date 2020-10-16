<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/** @var Magento\Framework\Registry $registry */
$registry = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(\Magento\Framework\Registry::class);

/** @var Magento\SalesRule\Model\Rule $rule */
$rule = $registry->registry('_fixture/Magento_SalesRule_Category');

$rule->delete();

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

/** @var $category \Magento\Catalog\Model\Category */
$category = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(\Magento\Catalog\Model\Category::class);
$category->load(66);
if ($category->getId()) {
    $category->delete();
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
