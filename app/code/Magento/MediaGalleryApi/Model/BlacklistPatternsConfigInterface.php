<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\MediaGalleryApi\Model;

/**
 * Returns list of blacklist regexp patterns
 */
interface BlacklistPatternsConfigInterface
{
    /**
     * Get regexp patterns
     *
     * @return array
     */
    public function get(): array;
}
