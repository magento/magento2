<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$productRepository = $objectManager->create('Magento\Catalog\Api\ProductRepositoryInterface');

/** @var $bundleSimpleProduct \Magento\Catalog\Model\Product */
$bundleSimpleProduct = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->create('Magento\Catalog\Model\Product');
$bundleSimpleProduct->setTypeId('simple')
    ->setAttributeSetId(4)
    ->setWebsiteIds([1])
    ->setName('Simple Product')
    ->setSku('bundle-simple')
    ->setPrice(10)
    ->setMetaTitle('meta title')
    ->setMetaKeyword('meta keyword')
    ->setMetaDescription('meta description')
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->setStockData(['use_config_manage_stock' => 0])
    ->save();

/** @var $product \Magento\Catalog\Model\Product */
$bundleProduct = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Catalog\Model\Product');
$bundleProduct->setTypeId('bundle')
    ->setAttributeSetId(4)
    ->setWebsiteIds([1])
    ->setName('Bundle Product')
    ->setSku('bundle-product')
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->setStockData(['use_config_manage_stock' => 1, 'qty' => 100, 'is_qty_decimal' => 0, 'is_in_stock' => 1])
    ->setPriceView(1)
    ->setPriceType(1)
    ->setPrice(10.0)
    ->setBundleOptionsData
    (
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
        [
            [
                [
                    'product_id' => $bundleSimpleProduct->getId(),
                    'selection_qty' => 1,
                    'selection_can_change_qty' => 1,
                    'delete' => ''
                ]
            ]
        ]
    )
    ->setCanSaveCustomOptions(true)
    ->setProductOptions(
        [
            [
                'id'        => 1,
                'option_id' => 0,
                'previous_group' => 'text',
                'title'     => 'Test Field',
                'type'      => 'field',
                'is_require' => 1,
                'sort_order' => 0,
                'price'     => 1,
                'price_type' => 'fixed',
                'sku'       => '1-text',
                'max_characters' => 100,
            ],
            [
                'id'        => 2,
                'option_id' => 0,
                'previous_group' => 'date',
                'title'     => 'Test Date and Time',
                'type'      => 'date_time',
                'is_require' => 1,
                'sort_order' => 0,
                'price'     => 2,
                'price_type' => 'fixed',
                'sku'       => '2-date',
            ],
            [
                'id'        => 3,
                'option_id' => 0,
                'previous_group' => 'select',
                'title'     => 'Test Select',
                'type'      => 'drop_down',
                'is_require' => 1,
                'sort_order' => 0,
                'values'    => [
                    [
                        'option_type_id' => -1,
                        'title'         => 'Option 1',
                        'price'         => 3,
                        'price_type'    => 'fixed',
                        'sku'           => '3-1-select',
                    ],
                    [
                        'option_type_id' => -1,
                        'title'         => 'Option 2',
                        'price'         => 3,
                        'price_type'    => 'fixed',
                        'sku'           => '3-2-select',
                    ],
                ]
            ],
            [
                'id'        => 4,
                'option_id' => 0,
                'previous_group' => 'select',
                'title'     => 'Test Radio',
                'type'      => 'radio',
                'is_require' => 1,
                'sort_order' => 0,
                'values'    => [
                    [
                        'option_type_id' => -1,
                        'title'         => 'Option 1',
                        'price'         => 3,
                        'price_type'    => 'fixed',
                        'sku'           => '4-1-radio',
                    ],
                    [
                        'option_type_id' => -1,
                        'title'         => 'Option 2',
                        'price'         => 3,
                        'price_type'    => 'fixed',
                        'sku'           => '4-2-radio',
                    ],
                ]
            ],
        ]
    )
    ->setHasOptions(true)
    ->save();
