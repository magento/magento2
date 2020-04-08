<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\MediaGalleryApi\Api;

/**
 * Delete media asset by exact or directory path
 * @api
 */
interface DeleteAssetsByPathsInterface
{
    /**
     * Delete media assets by path
     *
     * @param string[] $paths
     * @return void
     */
    public function execute(array $paths): void;
}
