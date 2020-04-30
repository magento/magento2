<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryApi\Api;

/**
 * Check if the path is blacklisted for media gallery.
 *
 * Directory path may be blacklisted if it's reserved by the system.
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
