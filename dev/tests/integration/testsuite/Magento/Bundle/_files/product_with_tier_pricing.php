<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/*
 * Since the bundle product creation GUI doesn't allow to choose values for bundled products' custom options,
 * bundled items should not contain products with required custom options.
 * However, if to create such a bundle product, it will be always out of stock.
 */
require __DIR__ . '/../../../Magento/Catalog/_files/products.php';

/** @var $product \Magento\Catalog\Model\Product */
$product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(\Magento\Catalog\Model\Product::class);
$product->setTypeId('bundle')
    ->setAttributeSetId(4)
    ->setWebsiteIds([1])
    ->setName('Bundle Product')
    ->setSku('bundle-product')
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->setStockData(['use_config_manage_stock' => 1, 'qty' => 100, 'is_qty_decimal' => 0, 'is_in_stock' => 1])
    ->setBundleOptionsData(
        [
            [
                'title' => 'Bundle Product Items',
                'default_title' => 'Bundle Product Items',
                'type' => 'select', 'required' => 1,
                'delete' => '',
            ],
        ]
    )
    ->setBundleSelectionsData(
        [[['product_id' => 1, 'selection_qty' => 1, 'selection_can_change_qty' => 1, 'delete' => '']]]
        // fixture product
    )->setTierPrice(
        [
           [
               'website_id' => 0,
               'cust_group' => \Magento\Customer\Model\GroupManagement::CUST_GROUP_ALL,
               'price_qty'  => 2,
               'price'      => 8,
               'percentage_value' => 8
           ],
            [
                'website_id' => 0,
                'cust_group' => \Magento\Customer\Model\GroupManagement::CUST_GROUP_ALL,
                'price_qty'  => 5,
                'price'      => 30,
                'percentage_value' => 30
            ],
           [
               'website_id' => 0,
               'cust_group' => \Magento\Customer\Model\GroupManagement::NOT_LOGGED_IN_ID,
               'price_qty'  => 3,
               'price'      => 20,
               'percentage_value' => 20
           ],
        ]
    )
    ->save();
