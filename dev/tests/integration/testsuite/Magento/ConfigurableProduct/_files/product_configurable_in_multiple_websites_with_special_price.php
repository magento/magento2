<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Bootstrap::getInstance()->reinitialize();

Resolver::getInstance()->requireDataFixture(
    'Magento/ConfigurableProduct/_files/product_configurable_in_multiple_websites.php'
);

/** @var WebsiteRepositoryInterface $repository */
$repository = Bootstrap::getObjectManager()->get(WebsiteRepositoryInterface::class);
$websiteId = $repository->get('test')->getId();

/** @var ProductRepositoryInterface $productRepository */
$productRepository = Bootstrap::getObjectManager()
    ->create(ProductRepositoryInterface::class);

$product = $productRepository->get('simple_20', true);
$product->setWebsiteIds([$websiteId]);
$product->setSpecialPrice('4');
$productRepository->save($product);

$product = $productRepository->get('simple_10', true);
$product->setWebsiteIds([1]);
$productRepository->save($product);
