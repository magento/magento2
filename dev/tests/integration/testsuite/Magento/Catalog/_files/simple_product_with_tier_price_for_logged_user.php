<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\Data\ProductTierPriceInterfaceFactory;
use Magento\Catalog\Api\Data\ProductTierPriceExtensionFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/product_simple.php');

$objectManager = Bootstrap::getObjectManager();
/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->create(ProductRepositoryInterface::class);
/** @var ProductTierPriceInterfaceFactory $tierPriceFactory */
$tierPriceFactory = $objectManager->get(ProductTierPriceInterfaceFactory::class);
/** @var  $tpExtensionAttributes */
$tpExtensionAttributesFactory = $objectManager->get(ProductTierPriceExtensionFactory::class);
$product = $productRepository->get('simple', false, null, true);
$adminWebsite = $objectManager->get(WebsiteRepositoryInterface::class)->get('admin');
$tierPrices = $product->getTierPrices() ?? [];
$tierPriceExtensionAttributes = $tpExtensionAttributesFactory->create()->setWebsiteId($adminWebsite->getId());
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
