<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\Data\ProductTierPriceExtensionFactory;
use Magento\Catalog\Api\Data\ProductTierPriceInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Model\Group;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture(
    'Magento/ConfigurableProduct/_files/product_configurable_with_custom_option_type_text.php'
);

$objectManager = Bootstrap::getObjectManager();
/** @var WebsiteRepositoryInterface $websiteRepository */
$websiteRepository = $objectManager->get(WebsiteRepositoryInterface::class);
/** @var ProductTierPriceInterfaceFactory $tierPriceFactory */
$tierPriceFactory = $objectManager->get(ProductTierPriceInterfaceFactory::class);
/** @var ProductTierPriceExtensionFactory $tpExtensionAttributeFactory */
$tpExtensionAttributeFactory = $objectManager->get(ProductTierPriceExtensionFactory::class);
/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->create(ProductRepositoryInterface::class);
$firstSimple = $productRepository->get('simple_10');
$firstSimple->setSpecialPrice(9);
$productRepository->save($firstSimple);

$secondSimple = $productRepository->get('simple_20');
$tierPriceExtensionAttribute = $tpExtensionAttributeFactory->create(
    ['data' => ['website_id' => $websiteRepository->get('admin')->getId(), 'percentage_value' => 25]]
);
$tierPrices[] = $tierPriceFactory
    ->create(['data' => ['customer_group_id' => Group::CUST_GROUP_ALL, 'qty' => 1]])
    ->setExtensionAttributes($tierPriceExtensionAttribute);
$secondSimple->setTierPrices($tierPrices);
$productRepository->save($secondSimple);
