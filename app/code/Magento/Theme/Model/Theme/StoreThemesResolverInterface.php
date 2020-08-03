<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Theme\Model\Theme;

use Magento\Store\Api\Data\StoreInterface;

/**
 * Store associated themes resolver.
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
