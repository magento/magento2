<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/ConfigurableProduct/_files/product_configurable_sku.php');
Resolver::getInstance()->requireDataFixture('Magento/Store/_files/second_website_with_two_stores.php');

$websiteRepository = Bootstrap::getObjectManager()->get(WebsiteRepositoryInterface::class);
$secondWebsite = $websiteRepository->get('test');

$productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);
$configurableProduct = $productRepository->get('configurable');
$configurableProduct->setWebsiteIds(array_merge($configurableProduct->getWebsiteIds(), [$secondWebsite->getId()]));
$productRepository->save($configurableProduct);
