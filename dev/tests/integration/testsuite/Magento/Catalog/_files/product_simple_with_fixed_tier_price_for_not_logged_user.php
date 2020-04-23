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

require __DIR__ . '/product_simple_tax_none.php';

$product = $productRepository->get('simple-product-tax-none');
/** @var WebsiteRepositoryInterface $websiteRepository */
$websiteRepository = $objectManager->get(WebsiteRepositoryInterface::class);
/** @var ProductTierPriceInterfaceFactory $tierPriceFactory */
$tierPriceFactory = $objectManager->get(ProductTierPriceInterfaceFactory::class);
/** @var ProductTierPriceExtensionFactory $tpExtensionAttributeFactory */
$tpExtensionAttributeFactory = $objectManager->get(ProductTierPriceExtensionFactory::class);
$adminWebsite = $websiteRepository->get('admin');
$tierPriceExtensionAttribute = $tpExtensionAttributeFactory->create(
    [
        'data' => [
            'website_id' => $adminWebsite->getId(),
        ]
    ]
);
$tierPrices[] = $tierPriceFactory->create(
    [
        'data' => [
            'customer_group_id' => Group::NOT_LOGGED_IN_ID,
            'qty' => 1,
            'value' => 30
        ]
    ]
)->setExtensionAttributes($tierPriceExtensionAttribute);
$product->setTierPrices($tierPrices);
$productRepository->save($product);
