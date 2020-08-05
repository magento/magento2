<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\MediaGalleryUiApi\Api;

/**
 * Class responsible to provide API access to system configuration related to the Media Gallery
 */
interface ConfigInterface
{
    /**
     * Check if grid UI is enabled for Magento media gallery
     *
     * @return bool
     */
    public function isEnabled(): bool;
}
