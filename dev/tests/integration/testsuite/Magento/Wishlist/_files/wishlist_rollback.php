<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
<<<<<<< HEAD
=======
declare(strict_types=1);
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc

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

require __DIR__ . '/../../../Magento/Catalog/_files/product_simple_rollback.php';
require __DIR__ . '/../../../Magento/Customer/_files/customer_rollback.php';
