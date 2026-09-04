<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryMetadata\Model;

use Magento\Framework\Exception\LocalizedException;

/**
 * Wrapper for the exif_read_data php function
 */
class ExifReader
{
    /**
     * Returns result of exif_read_data function
     *
     * @param string $filePath
     * @return array|false
     * @throws LocalizedException
     */
    public function get(string $filePath)
    {
        if (!is_callable('exif_read_data')) {
            throw new LocalizedException(
                __('exif_read_data() must be enabled in php configuration')
            );
        }

        return exif_read_data($filePath);
    }
}
