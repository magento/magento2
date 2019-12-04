<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\Data\ProductTierPriceExtensionFactory;
use Magento\Catalog\Api\Data\ProductTierPriceInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;

/** @var ObjectManager $objectManager */
$objectManager = Bootstrap::getObjectManager();
/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->get(ProductRepositoryInterface::class);
/** @var ProductTierPriceInterfaceFactory $tierPriceFactory */
$tierPriceFactory = $objectManager->get(ProductTierPriceInterfaceFactory::class);
/** @var ProductTierPriceExtensionFactory $tierPriceExtensionAttributesFactory */
$tierPriceExtensionAttributesFactory = $objectManager->get(ProductTierPriceExtensionFactory::class);
/** @var WebsiteRepositoryInterface $websiteRepository */
$websiteRepository = $objectManager->get(WebsiteRepositoryInterface::class);
$adminWebsite = $websiteRepository->get('admin');
$product = $productRepository->get('simple', false, null, true);
$tierPrices = $product->getTierPrices() ?? [];
$tierPriceExtensionAttributes = $tierPriceExtensionAttributesFactory->create()->setWebsiteId($adminWebsite->getId());
$tierPrices[] = $tierPriceFactory->create(
    [
        'data' => [
            'customer_group_id' => 1,
            'qty' => 3,
            'value' => 1
        ]
    ]
)->setExtensionAttributes($tierPriceExtensionAttributes);
$product->setTierPrices($tierPrices);
$productRepository->save($product);
