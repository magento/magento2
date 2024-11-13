<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\Data\ProductExtensionInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture(
    'Magento/Catalog/_files/product_image.php'
);
Resolver::getInstance()->requireDataFixture(
    'Magento/Swatches/_files/configurable_product_visual_swatch_attribute.php'
);

$objectManager = Bootstrap::getObjectManager();
/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->get(ProductRepositoryInterface::class);
$configurableProduct = $productRepository->get('configurable');
$children = $configurableProduct->getTypeInstance()->getUsedProducts($configurableProduct);
$images = ['magento_image.jpg', 'magento_small_image.jpg', 'magento_thumbnail.jpg'];
foreach ($children as $index => $product) {
    $product->setImage('/m/a/' . $images[$index])
        ->setSmallImage('/m/a/' . $images[$index])
        ->setThumbnail('/m/a/' . $images[$index])
        ->setData('media_gallery', ['images' => [
            [
                'file' => '/m/a/' . $images[$index],
                'position' => 1,
                'label' => 'Image Alt Text',
                'disabled' => 0,
                'media_type' => 'image',
            ],
        ]])
        ->setCanSaveCustomOptions(true)
        ->save();
}
