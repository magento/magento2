<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/** @var $product \Magento\Catalog\Model\Product */
$product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->create(\Magento\Catalog\Model\Product::class);
$product->isObjectNew(true);
$product->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE)
    ->setId(1)
    ->setAttributeSetId(4)
    ->setWebsiteIds([1])
    ->setName('Simple Product')
    ->setSku('simple')
    ->setPrice(10)
    ->setWeight(1)
    ->setShortDescription("Short description")
    ->setTaxClassId(0)
    ->setTierPrice(
        [
            [
                'website_id' => 0,
                'cust_group' => \Magento\Customer\Model\Group::CUST_GROUP_ALL,
                'price_qty'  => 2,
                'price'      => 8,
            ],
            [
                'website_id' => 0,
                'cust_group' => \Magento\Customer\Model\Group::CUST_GROUP_ALL,
                'price_qty'  => 5,
                'price'      => 5,
            ],
            [
                'website_id' => 0,
                'cust_group' => \Magento\Customer\Model\Group::NOT_LOGGED_IN_ID,
                'price_qty'  => 3,
                'price'      => 5,
            ],
        ]
    )
    ->setDescription('Description with <b>html tag</b>')
    ->setMetaTitle('meta title')
    ->setMetaKeyword('meta keyword')
    ->setMetaDescription('meta description')
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->setCategoryIds([2])
    ->setStockData(
        [
            'use_config_manage_stock'   => 1,
            'qty'                       => 100,
            'is_qty_decimal'            => 0,
            'is_in_stock'               => 1,
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
