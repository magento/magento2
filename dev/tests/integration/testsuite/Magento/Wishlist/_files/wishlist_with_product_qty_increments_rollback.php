<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/** @var \Magento\Framework\ObjectManagerInterface $objectManager */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var \Magento\Framework\Registry $registry */
$registry = $objectManager->get(\Magento\Framework\Registry::class);
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

/** @var \Magento\Wishlist\Model\Wishlist $wishlist */
$wishlist = $objectManager->create(\Magento\Wishlist\Model\Wishlist::class);
$wishlist->loadByCustomerId(1);
$wishlist->delete();

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);

require __DIR__ . '/../../../Magento/Catalog/_files/product_special_price_rollback.php';
require __DIR__ . '/../../../Magento/Customer/_files/customer_rollback.php';
