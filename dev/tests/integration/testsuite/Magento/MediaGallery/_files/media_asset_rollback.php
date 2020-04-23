<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\MediaGalleryApi\Model\Asset\Command\DeleteByPathInterface;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();

/** @var DeleteByPathInterface $mediaSave */
$mediaAssetDelete = $objectManager->get(DeleteByPathInterface::class);

try {
    $mediaAssetDelete->execute('testDirectory/path.jpg');
} catch (\Exception $exception) {

}
