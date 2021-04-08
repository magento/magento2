<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\Data\ProductTierPriceExtensionFactory;
use Magento\Catalog\Api\Data\ProductTierPriceInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Customer\Model\Group;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
/** @var ProductTierPriceInterfaceFactory $tierPriceFactory */
$tierPriceFactory = $objectManager->get(ProductTierPriceInterfaceFactory::class);
/** @var ProductTierPriceExtensionFactory $tpExtensionAttributes */
$tpExtensionAttributesFactory = $objectManager->get(ProductTierPriceExtensionFactory::class);

/** @var WebsiteInterface $adminWebsite */
$adminWebsite = $objectManager->get(WebsiteRepositoryInterface::class)->get('admin');
$tierPriceExtensionAttributes = $tpExtensionAttributesFactory->create()
    ->setWebsiteId($adminWebsite->getId())
    ->setPercentageValue(50);

$tierPrice = $tierPriceFactory->create(
    [
        'data' => [
            'customer_group_id' => Group::CUST_GROUP_ALL,
            'qty' => 2,
        ],
    ]
)->setExtensionAttributes($tierPriceExtensionAttributes);

/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->get(ProductRepositoryInterface::class);
$productNumber = 1;
foreach ([10, 20, 30] as $price) {
    /** @var $product Product */
    $product = $objectManager->create(Product::class);
    $product->setTypeId(Type::TYPE_SIMPLE)
        ->setAttributeSetId($product->getDefaultAttributeSetId())
        ->setWebsiteIds([1])
        ->setName('Simple Product ' . $productNumber)
        ->setSku('simple_' . $productNumber)
        ->setPrice($price)
        ->setWeight(1)
        ->setTierPrices([$tierPrice])
        ->setVisibility(Visibility::VISIBILITY_BOTH)
        ->setStatus(Status::STATUS_ENABLED)
        ->setStockData(
            [
                'use_config_manage_stock' => 1,
                'qty' => 100,
                'is_qty_decimal' => 0,
                'is_in_stock' => 1,
            ]
        );

    $productRepository->save($product);
    $productNumber++;
}
