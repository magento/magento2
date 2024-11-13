<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\MediaGalleryApi\Api\DeleteAssetsByPathsInterface;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();

/** @var DeleteAssetsByPathsInterface $mediaSave */
$mediaAssetDelete = $objectManager->get(DeleteAssetsByPathsInterface::class);

try {
    $mediaAssetDelete->execute(['testDirectory/year_ago_loaded_img.jpg']);
} catch (\Exception $exception) {
    // already deleted
}
