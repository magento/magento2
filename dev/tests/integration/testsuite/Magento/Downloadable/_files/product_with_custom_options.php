<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

$downloadableProduct = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->create('Magento\Catalog\Model\Product');
$downloadableProduct->setTypeId(\Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE)
    ->setAttributeSetId(4)
    ->setWebsiteIds([1])
    ->setName('Downloadable Product')
    ->setSku('downloadable-product')
    ->setPrice(10)
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->setLinksPurchasedSeparately(true)
    ->setDownloadableData(
        [
            'link' => [
                [
                    'title' => 'Downloadable Product Link',
                    'type' => \Magento\Downloadable\Helper\Download::LINK_TYPE_URL,
                    'is_shareable' => \Magento\Downloadable\Model\Link::LINK_SHAREABLE_CONFIG,
                    'link_url' => 'http://example.com/downloadable.txt',
                    'link_id' => 0,
                    'is_delete' => null,
                ],
            ],
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
