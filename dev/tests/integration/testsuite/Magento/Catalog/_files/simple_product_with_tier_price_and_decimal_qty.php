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
$productRepository = $objectManager->get(ProductRepositoryInterface::class);
/** @var ProductTierPriceInterfaceFactory $tierPriceFactory */
$tierPriceFactory = $objectManager->get(ProductTierPriceInterfaceFactory::class);
/** @var ProductTierPriceExtensionFactory $tierPriceExtensionAttributesFactory */
$tierPriceExtensionAttributesFactory = $objectManager->get(ProductTierPriceExtensionFactory::class);
$product = $productRepository->get('simple', false, null, true);
$product->setStockData(['use_config_manage_stock' => 1, 'qty' => 100, 'is_qty_decimal' => 1, 'is_in_stock' => 1, 'min_sale_qty' => 0.5]);
$adminWebsite = $objectManager->get(WebsiteRepositoryInterface::class)->get('admin');
$tierPrices = $product->getTierPrices() ?? [];
$product->setPrice(3.99);
$tierPriceExtensionAttributes = $tierPriceExtensionAttributesFactory->create()->setWebsiteId($adminWebsite->getId());
$tierPrices[] = $tierPriceFactory->create(
    [
        'data' => [
            'customer_group_id' => \Magento\Customer\Model\Group::CUST_GROUP_ALL,
            'qty' => 0.5,
            'value' => 2.99
        ]
    ]
)->setExtensionAttributes($tierPriceExtensionAttributes);
$product->setTierPrices($tierPrices);
$productRepository->save($product);
