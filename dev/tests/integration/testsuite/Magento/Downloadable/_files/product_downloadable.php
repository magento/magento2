<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
)->setLinksPurchasedSeparately(
    true
)->setDownloadableData(
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
)->save();
