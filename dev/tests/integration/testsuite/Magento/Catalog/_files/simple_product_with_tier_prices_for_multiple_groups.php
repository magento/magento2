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
$productRepository = $objectManager->create(ProductRepositoryInterface::class);
$tierPriceFactory = $objectManager->get(ProductTierPriceInterfaceFactory::class);
$tpExtensionAttributesFactory = $objectManager->get(ProductTierPriceExtensionFactory::class);
$product = $productRepository->get('simple', false, null, true);
$adminWebsite = $objectManager->get(WebsiteRepositoryInterface::class)->get('admin');
$tierPriceExtensionAttributes = $tpExtensionAttributesFactory->create()->setWebsiteId($adminWebsite->getId());
$pricesForCustomerGroupsInput = [
    [
        'customer_group_id' => 1,
        'percentage_value'=> null,
        'qty'=> 2,
        'value'=> 9.25
    ],
    [
        'customer_group_id' => 1,
        'percentage_value'=> null,
        'qty'=> 3,
        'value'=> 8.25
    ],
    [
        'customer_group_id' => 1,
        'percentage_value'=> null,
        'qty'=> 5,
        'value'=> 7.25
    ],
    [
        'customer_group_id' => 2,
        'percentage_value'=> null,
        'qty'=> 2,
        'value'=> 9
    ],
    [
        'customer_group_id' => 2,
        'percentage_value'=> null,
        'qty'=> 3,
        'value'=> 8
    ],
    [
        'customer_group_id' => 2,
        'percentage_value'=> null,
        'qty'=> 5,
        'value'=> 7
    ]
];
$productTierPrices = [];
foreach ($pricesForCustomerGroupsInput as $price) {
    $productTierPrices[] = $tierPriceFactory->create(
        [
            'data' => $price
        ]
    )->setExtensionAttributes($tierPriceExtensionAttributes);
}
$product->setTierPrices($productTierPrices);
$productRepository->save($product);
