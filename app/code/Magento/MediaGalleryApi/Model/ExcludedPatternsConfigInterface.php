<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\MediaGalleryApi\Model;

/**
 * Returns list of excluded regexp patterns
 * @api
 */
interface ExcludedPatternsConfigInterface
{
    /**
     * Get regexp patterns
     *
     * @return array
     */
    public function get(): array;
}
