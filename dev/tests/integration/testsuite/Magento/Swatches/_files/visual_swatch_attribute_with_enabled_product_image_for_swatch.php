<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Model\Product;

require __DIR__ . '/visual_swatch_attribute_with_different_options_type.php';
require __DIR__ . '/../../../Magento/ConfigurableProduct/_files/configurable_products.php';
require __DIR__ . '/../../../Magento/Catalog/_files/product_image.php';

// set 'Product Image for Swatch' for attribute
$attribute->setData('use_product_image_for_swatch', 1);
$attributeRepository->save($attribute);

// get first child and set image
$childrenProducts = $product->getTypeInstance()->getUsedProducts($product);
/** @var Product $firstChildSimpleProduct */
$firstChildSimpleProduct = array_shift($childrenProducts);
$firstChildSimpleProduct
    ->setImage('/m/a/magento_image.jpg')
    ->setSmallImage('/m/a/magento_image.jpg')
    ->setThumbnail('/m/a/magento_image.jpg')
    ->setData('media_gallery', ['images' => [
        [
            'file' => '/m/a/magento_image.jpg',
            'position' => 1,
            'label' => 'Image Alt Text',
            'disabled' => 0,
            'media_type' => 'image'
        ],
    ]])
    ->save();
