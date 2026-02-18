<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

/** @var \Magento\Framework\ObjectManagerInterface $objectManager */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var \Magento\Wishlist\Model\Wishlist $wishlist */
$wishlist = $objectManager->create(\Magento\Wishlist\Model\Wishlist::class);
$wishlist->loadByCustomerId(1);
$wishlist->delete();
$wishlist->loadByCustomerId(2);
$wishlist->delete();

Resolver::getInstance()->requireDataFixture('Magento/Customer/_files/two_customers_rollback.php');
Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/product_simple_rollback.php');
