<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Catalog\Api\Data\ProductInterface;

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var \Magento\Framework\Registry $registry */
$registry = $objectManager
    ->get(\Magento\Framework\Registry::class);
$registry->unregister("isSecureArea");
$registry->register("isSecureArea", true);

/** @var Magento\Store\Model\Website $website */
$website = $objectManager->create(\Magento\Store\Model\Website::class);
$website->load('second_website');
$website->delete();

/** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);

try {
    $firstProduct = $productRepository->get('unique-simple-azaza');
    $firstProduct->delete();
} catch (\Magento\Framework\Exception\NoSuchEntityException $exception) {
    //Product already removed
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);

$objectManager->get(\Magento\Store\Model\StoreManagerInterface::class)->reinitStores();
