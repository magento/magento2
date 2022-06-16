<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Framework\Registry;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Wishlist\Model\ResourceModel\Wishlist as WishlistResource;
use Magento\Wishlist\Model\Wishlist;
use Magento\Wishlist\Model\WishlistFactory;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

$objectManager = Bootstrap::getObjectManager();
/** @var Registry $registry */
$registry = $objectManager->get(Registry::class);
/** @var WishlistResource $wishListResource */
$wishListResource = $objectManager->get(WishlistResource::class);
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);
/** @var Wishlist $wishlist */
$wishlist = $objectManager->get(WishlistFactory::class)->create();
$wishlist->loadByCustomerId(1);
if ($wishlist->getId()) {
    $wishListResource->delete($wishlist);
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);

Resolver::getInstance()
    ->requireDataFixture('Magento/Catalog/_files/simple_products_not_visible_individually_rollback.php');
Resolver::getInstance()->requireDataFixture('Magento/Customer/_files/customer_rollback.php');
