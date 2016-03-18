<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/** @var \Magento\Framework\Registry $registry */
$registry = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Framework\Registry');

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

/** @var $product \Magento\Catalog\Model\Product */
$product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Catalog\Model\Product');
$product->load(1);
if ($product->getId()) {
    $product->delete();
}

/** @var $customDesignProduct \Magento\Catalog\Model\Product */
$customDesignProduct = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->create('Magento\Catalog\Model\Product');
$customDesignProduct->load(2);
if ($customDesignProduct->getId()) {
    $customDesignProduct->delete();
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
