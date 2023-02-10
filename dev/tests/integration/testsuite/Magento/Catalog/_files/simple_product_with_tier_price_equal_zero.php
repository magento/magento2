<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\Data\ProductTierPriceExtension;
use Magento\Catalog\Api\Data\ProductTierPriceInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/product_simple_without_custom_options.php');

$objectManager = Bootstrap::getObjectManager();

$adminWebsite = $objectManager->get(WebsiteRepositoryInterface::class)
    ->get('admin');
$tierPriceExtensionAttributes = $objectManager->create(ProductTierPriceExtension::class)
    ->setWebsiteId($adminWebsite->getId());
$tierPrices = [];
$tierPrice = $objectManager->create(ProductTierPriceInterface::class)
    ->setCustomerGroupId(0)
    ->setQty(1)
    ->setValue(0)
    ->setExtensionAttributes($tierPriceExtensionAttributes);
$tierPrices[] = $tierPrice;

$productRepository = $objectManager->get(ProductRepositoryInterface::class);
$product = $productRepository->get('simple-2', false, null, true);
$product->setTierPrices($tierPrices);
$productRepository->save($product);
