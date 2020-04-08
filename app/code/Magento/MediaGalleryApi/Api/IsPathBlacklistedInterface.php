<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryApi\Api;

/**
 * Directory paths that are reserved by system and not be included in the media gallery
 * @api
 */
interface IsPathBlacklistedInterface
{
    /**
     * Check if the path is excluded from displaying and processing in the media gallery
     *
     * @param string $path
     * @return bool
     */
    public function execute(string $path): bool;
}
