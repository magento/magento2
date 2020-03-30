<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryApi\Model\Directory;

/**
 * Directory paths that should not be included in the media gallery
 * @api
 */
interface ExcludedInterface
{
    /**
     * Check if the path is excluded from displaying in the media gallery
     *
     * @param string $path
     * @return bool
     */
    public function isExcluded(string $path): bool;
}
