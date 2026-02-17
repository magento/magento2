<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryRenditionsApi\Api;

use Magento\Framework\Exception\LocalizedException;

/**
 * Based on media assset path provides path to an optimized image version for insertion to the content
 * @api
 */
interface GetRenditionPathInterface
{
    /**
     * Get Renditions image path
     *
     * @param string $path
     * @return string
     * @throws LocalizedException
     */
    public function execute(string $path): string;
}
