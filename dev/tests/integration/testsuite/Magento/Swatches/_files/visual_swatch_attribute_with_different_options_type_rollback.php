<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Swatches\Helper\Media as SwatchesMedia;
use Magento\TestFramework\Helper\Bootstrap;

/** @var WriteInterface $mediaDirectory */
$mediaDirectory = Bootstrap::getObjectManager()->get(Filesystem::class)
    ->getDirectoryWrite(
        DirectoryList::MEDIA
    );

/** @var SwatchesMedia $swatchesMedia */
$swatchesMedia = Bootstrap::getObjectManager()->get(SwatchesMedia::class);

$testImageName = 'visual_swatch_attribute_option_type_image.jpg';
$testImageSwatchPath = $swatchesMedia->getAttributeSwatchPath($testImageName);
$mediaDirectory->delete($testImageSwatchPath);

$imageConfig = $swatchesMedia->getImageConfig();
$swatchTypes = ['swatch_image', 'swatch_thumb'];

foreach ($swatchTypes as $swatchType) {
    $absolutePath = $mediaDirectory->getAbsolutePath($swatchesMedia->getSwatchCachePath($swatchType));
    $swatchTypePath = $absolutePath . $swatchesMedia->getFolderNameSize($swatchType, $imageConfig) .
        '/' . $testImageName;
    $mediaDirectory->delete($swatchTypePath);
}
