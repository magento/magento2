<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\MediaGallerySynchronizationApi\Model;

use Magento\Framework\Exception\FileSystemException;
use Magento\MediaGalleryApi\Api\Data\AssetInterface;

/**
 * Create media asset object from the media file
 * @api
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
