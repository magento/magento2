<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\Data\ProductTierPriceExtensionFactory;
use Magento\Catalog\Api\Data\ProductTierPriceInterfaceFactory;
use Magento\Customer\Model\Group;
use Magento\Store\Api\WebsiteRepositoryInterface;

require __DIR__ . '/product_configurable_with_custom_option_type_text.php';

/** @var WebsiteRepositoryInterface $websiteRepository */
$websiteRepository = $objectManager->get(WebsiteRepositoryInterface::class);
/** @var ProductTierPriceInterfaceFactory $tierPriceFactory */
$tierPriceFactory = $objectManager->get(ProductTierPriceInterfaceFactory::class);
/** @var ProductTierPriceExtensionFactory $tpExtensionAttributeFactory */
$tpExtensionAttributeFactory = $objectManager->get(ProductTierPriceExtensionFactory::class);

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
