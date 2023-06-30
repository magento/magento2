<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGallerySynchronizationApi\Model;

use Magento\Framework\Exception\FileSystemException;
use Magento\MediaGalleryApi\Api\Data\AssetInterface;

/**
 * Create media asset object from the media file
 */
interface CreateAssetFromFileInterface
{
    /**
     * Create media asset object from the media file
     *
     * @param string $path
     * @return AssetInterface
     * @throws FileSystemException
     */
    public function execute(string $path): AssetInterface;
}
