<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryRenditions\Model;

use Magento\MediaGalleryRenditionsApi\Api\GetRenditionPathInterface;

class GetRenditionPath implements GetRenditionPathInterface
{
    private const RENDITIONS_DIRECTORY_NAME = '.renditions';

    /**
     * Returns Rendition image path
     *
     * @param string $path
     * @return string
     */
    public function execute(string $path): string
    {
        return self::RENDITIONS_DIRECTORY_NAME . '/' . ltrim($path, '/');
    }
}
