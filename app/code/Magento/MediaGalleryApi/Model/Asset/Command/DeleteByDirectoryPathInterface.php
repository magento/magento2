<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\MediaGalleryApi\Model\Asset\Command;

/**
 * A command represents the media gallery assets delete action. A media gallery asset is filtered by directory
 * path value.
 * @deprecated 101.0.0 use \Magento\MediaGalleryApi\Api\DeleteAssetsByPathsInterface instead
 * @see \Magento\MediaGalleryApi\Api\DeleteAssetsByPathsInterface
 */
interface DeleteByDirectoryPathInterface
{
    /**
     * Delete media assets by directory path
     *
     * @param string $directoryPath
     * @return void
     */
    public function execute(string $directoryPath): void;
}
