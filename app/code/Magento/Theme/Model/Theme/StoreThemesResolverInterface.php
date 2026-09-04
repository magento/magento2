<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Theme\Model\Theme;

use Magento\Store\Api\Data\StoreInterface;

/**
 * Store associated themes resolver.
 *
 * @api
 */
interface StoreThemesResolverInterface
{
    /**
     * Get themes associated with a store view
     *
     * @param StoreInterface $store
     * @return int[]
     */
    public function getThemes(StoreInterface $store): array;
}
