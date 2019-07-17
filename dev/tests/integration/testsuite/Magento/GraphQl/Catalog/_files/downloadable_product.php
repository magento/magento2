<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Downloadable\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Framework\Api\DataObjectHelper;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Downloadable\Api\Data\LinkInterfaceFactory;
use Magento\Downloadable\Api\Data\LinkInterface;
use Magento\Downloadable\Model\Link;
use Magento\Downloadable\Helper\Download;
use Magento\Framework\Api\ExtensionAttributesFactory;

$objectManager = Bootstrap::getObjectManager();
/** @var ProductInterfaceFactory $productFactory */
$productFactory = $objectManager->get(ProductInterfaceFactory::class);
/** @var DataObjectHelper $dataObjectHelper */
$dataObjectHelper = Bootstrap::getObjectManager()->get(DataObjectHelper::class);
/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->get(ProductRepositoryInterface::class);
/** @var LinkInterfaceFactory $linkFactory */
$linkFactory = $objectManager->get(LinkInterfaceFactory::class);
/** @var ExtensionAttributesFactory $extensionAttributesFactory */
$extensionAttributesFactory = $objectManager->get(ExtensionAttributesFactory::class);

$link = $linkFactory->create();
$linkData = [
    LINK::KEY_TITLE               => 'Downloadable Product Link',
    LINK::KEY_LINK_TYPE           => Download::LINK_TYPE_URL,
    LINK::KEY_IS_SHAREABLE        => Link::LINK_SHAREABLE_CONFIG,
    LINK::KEY_LINK_URL            => 'http://example.com/downloadable.txt',
    LINK::KEY_PRICE               => 0,
    LINK::KEY_NUMBER_OF_DOWNLOADS => 0,
    LINK::KEY_SORT_ORDER          => 1,
];

$dataObjectHelper->populateWithArray($link, $linkData, LinkInterface::class);
$extensionAttributes = $extensionAttributesFactory->create(ProductInterface::class);
$extensionAttributes->setDownloadableProductLinks([$link]);

$product = $productFactory->create();
$productData = [
    ProductInterface::TYPE_ID                  => Type::TYPE_DOWNLOADABLE,
    ProductInterface::ATTRIBUTE_SET_ID         => 4,
    ProductInterface::SKU                      => 'downloadable_product',
    ProductInterface::NAME                     => 'Downloadable Product',
    ProductInterface::PRICE                    => 10,
    ProductInterface::VISIBILITY               => Visibility::VISIBILITY_BOTH,
    ProductInterface::STATUS                   => Status::STATUS_ENABLED,
    ProductInterface::EXTENSION_ATTRIBUTES_KEY => $extensionAttributes
];

$dataObjectHelper->populateWithArray($product, $productData, ProductInterface::class);
/** Out of interface */
$product
    ->setWebsiteIds([1])
    ->setStockData(
        [
        'qty'            => 85.5,
        'is_in_stock'    => true,
        'manage_stock'   => true,
        'is_qty_decimal' => true
        ]
    );

$productRepository->save($product);
