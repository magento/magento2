<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

/** @var \Magento\Framework\ObjectManagerInterface $objectManager */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var \Magento\Wishlist\Model\Wishlist $wishlist */
$wishlist = $objectManager->create(\Magento\Wishlist\Model\Wishlist::class);
$wishlist->loadByCustomerId(1);
$wishlist->delete();
$wishlist->loadByCustomerId(2);
$wishlist->delete();

require __DIR__ . '/../../../Magento/Customer/_files/two_customers_rollback.php';
require __DIR__ . '/../../../Magento/Catalog/_files/product_simple_rollback.php';
