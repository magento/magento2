<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
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
