<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\MediaGalleryApi\Api;

/**
 * Delete media assets by exact or directory paths
 * @api
 * @since 101.0.0
 */
interface DeleteAssetsByPathsInterface
{
    /**
     * Delete media assets by paths. Removes all the assets which paths start with provided paths
     *
     * @param string[] $paths
     * @return void
     * @since 101.0.0
     */
    public function execute(array $paths): void;
}
