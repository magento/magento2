<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

$product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Catalog\Model\Product');
$product->setTypeId(
    \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE
)->setId(
    1
)->setAttributeSetId(
    4
)->setWebsiteIds(
    [1]
)->setName(
    'Downloadable Product'
)->setSku(
    'downloadable-product'
)->setPrice(
    10
)->setVisibility(
    \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH
)->setStatus(
    \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED
)->setDownloadableData(
    [
        'link' => [
            [
                'link_id' => 0,
                'product_id' => 1,
                'sort_order' => '0',
                'title' => 'Downloadable Product Link',
                'sample' => [
                    'type' => \Magento\Downloadable\Helper\Download::LINK_TYPE_FILE,
                    'url' => null,
                    'file' => json_encode(
                        [
                            [
                                'file' => '/n/d/jellyfish_1_3.jpg',
                                'name' => 'jellyfish_1_3.jpg',
                                'size' => 54565,
                                'status' => 0,
                            ],
                        ]
                    ),
                ],
                'file' => json_encode(
                    [
                        [
                            'file' => '/j/e/jellyfish_2_4.jpg',
                            'name' => 'jellyfish_2_4.jpg',
                            'size' => 56644,
                            'status' => 0,
                        ],
                    ]
                ),
                'type' => \Magento\Downloadable\Helper\Download::LINK_TYPE_FILE,
                'is_shareable' => \Magento\Downloadable\Model\Link::LINK_SHAREABLE_CONFIG,
                'link_url' => null,
                'is_delete' => 0,
                'number_of_downloads' => 15,
                'price' => 15.00,
            ],
        ],
        'sample' => [
            [
                'is_delete' => 0,
                'sample_id' => 0,
                'title' => 'Downloadable Product Sample Title',
                'type' => \Magento\Downloadable\Helper\Download::LINK_TYPE_FILE,
                'file' => json_encode(
                    [
                        [
                            'file' => '/f/u/jellyfish_1_4.jpg',
                            'name' => 'jellyfish_1_4.jpg',
                            'size' => 1024,
                            'status' => 0,
                        ],
                    ]
                ),
                'sample_url' => null,
                'sort_order' => '0',
            ],
        ],
    ]
)->save();
