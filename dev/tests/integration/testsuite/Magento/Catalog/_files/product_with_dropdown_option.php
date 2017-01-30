<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/** @var $product \Magento\Catalog\Model\Product */
$product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Catalog\Model\Product');
$product->setTypeId(
    'simple'
)->setId(
    1
)->setAttributeSetId(
    4
)->setWebsiteIds(
    [1]
)->setName(
    'Simple Product With Custom Options'
)->setSku(
    'simple_dropdown_option'
)->setPrice(
    200
)->setMetaTitle(
    'meta title'
)->setMetaKeyword(
    'meta keyword'
)->setMetaDescription(
    'meta description'
)->setVisibility(
    \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH
)->setStatus(
    \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED
)->setCanSaveCustomOptions(
    true
)->setProductOptions(
    [
        [
            'title' => 'drop_down option',
            'type' => 'drop_down',
            'is_require' => true,
            'sort_order' => 4,
            'values' => [
                [
                    'title' => 'drop_down option 1',
                    'price' => 10,
                    'price_type' => 'fixed',
                    'sku' => 'drop_down option 1 sku',
                    'sort_order' => 1,
                ],
                [
                    'title' => 'drop_down option 2',
                    'price' => 20,
                    'price_type' => 'percent',
                    'sku' => 'drop_down option 2 sku',
                    'sort_order' => 2,
                ],
            ],
        ]
    ]
)->save();
