<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

use Magento\MediaGalleryApi\Api\DeleteAssetsByPathsInterface;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();

/** @var DeleteAssetsByPathsInterface $mediaSave */
$mediaAssetDelete = $objectManager->get(DeleteAssetsByPathsInterface::class);

try {
    $mediaAssetDelete->execute(['testDirectory/path.jpg']);
} catch (\Exception $exception) {

}
