<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\Store\Model\Store;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/product_image.php');

$objectManager = Bootstrap::getObjectManager();
/** @var ProductResource $productResource */
$productResource = $objectManager->get(ProductResource::class);
/** @var ProductInterfaceFactory $productFactory */
$productFactory = $objectManager->get(ProductInterfaceFactory::class);
/** @var WebsiteRepositoryInterface $websiteRepository */
$websiteRepository = $objectManager->get(WebsiteRepositoryInterface::class);
$baseWebsiteId = (int)$websiteRepository->get('base')->getId();
$product = $productFactory->create();
$product->setTypeId(Type::TYPE_SIMPLE)
    ->setAttributeSetId($product->getDefaultAttributeSetId())
    ->setWebsiteIds([$baseWebsiteId])
    ->setName('Simple product with disabled image')
    ->setSku('simple_with_disabled_img')
    ->setPrice(10)
    ->setMetaTitle('meta title2')
    ->setMetaKeyword('meta keyword2')
    ->setMetaDescription('meta description2')
    ->setVisibility(Visibility::VISIBILITY_BOTH)
    ->setStatus(Status::STATUS_ENABLED)
    ->setStockData(['use_config_manage_stock' => 0])
    ->setStoreId(Store::DEFAULT_STORE_ID)
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

$productResource->save($product);
