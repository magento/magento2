<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Store\Model\Store;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/product_image.php');
Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/product_simple.php');

$objectManager = Bootstrap::getObjectManager();
/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->get(ProductRepositoryInterface::class);
$productRepository->cleanCache();
$product = $productRepository->get('simple');
$product->setStoreId(Store::DEFAULT_STORE_ID)
    ->setImage('/m/a/magento_image.jpg')
    ->setSmallImage('/m/a/magento_image.jpg')
    ->setThumbnail('/m/a/magento_image.jpg')
    ->setData(
        'media_gallery',
        [
            'images' => [
                [
                    ProductAttributeMediaGalleryEntryInterface::FILE => '/m/a/magento_image.jpg',
                    ProductAttributeMediaGalleryEntryInterface::POSITION => 1,
                    ProductAttributeMediaGalleryEntryInterface::LABEL => 'Image Alt Text',
                    ProductAttributeMediaGalleryEntryInterface::DISABLED => 1,
                    ProductAttributeMediaGalleryEntryInterface::MEDIA_TYPE => 'image',
                ],
            ],
        ]
    )
    ->setCanSaveCustomOptions(true);

$productRepository->save($product);
