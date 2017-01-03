<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Catalog\Api\Data\ProductInterface;

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
/** @var Magento\Store\Model\Website $website */
$website = $objectManager->get(Magento\Store\Model\Website::class);

$website->setData(
    [
        'code' => 'second_website',
        'name' => 'Test Website',
    ]
);

$website->save();

$objectManager->get(\Magento\Store\Model\StoreManagerInterface::class)->reinitStores();

/** @var $product \Magento\Catalog\Model\Product */
$product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->create(ProductInterface::class);
$product
    ->setTypeId('simple')
    ->setAttributeSetId(4)
    ->setWebsiteIds([1, $website->getId()])
    ->setName('Simple Product')
    ->setSku('unique-simple-azaza')
    ->setPrice(10)
    ->setMetaTitle('meta title')
    ->setMetaKeyword('meta keyword')
    ->setMetaDescription('meta description')
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->setStockData(['use_config_manage_stock' => 0])
    ->save();
