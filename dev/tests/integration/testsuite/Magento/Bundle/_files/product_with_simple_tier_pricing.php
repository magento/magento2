<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

require __DIR__ . '/../../../Magento/Catalog/_files/product_simple.php';

/** @var $productFactory Magento\Catalog\Model\ProductFactory */
$productFactory = $objectManager->create(\Magento\Catalog\Model\ProductFactory::class);
/** @var $bundleProduct \Magento\Catalog\Model\Product */
$bundleProduct = $productFactory->create();
$bundleProduct->setTypeId('bundle')
    ->setAttributeSetId($product->getDefaultAttributeSetId())
    ->setWebsiteIds([1])
    ->setPriceType(\Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC)
    ->setPriceView(1)
    ->setName('Bundle Product')
    ->setSku('bundle-product')
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->setStockData([
        'use_config_manage_stock' => 1,
        'qty' => 100,
        'is_qty_decimal' => 0,
        'is_in_stock' => 1,
    ])
    ->setBundleOptionsData(
        [
            [
                'title' => 'Bundle Product Items',
                'default_title' => 'Bundle Product Items',
                'type' => 'checkbox',
                'required' => 1,
                'delete' => '',
            ],
        ]
    )
    ->setBundleSelectionsData(
        [[['product_id' => $product->getId(), 'selection_qty' => 1, 'delete' => '']]]
    );
$productRepository->save($bundleProduct);
