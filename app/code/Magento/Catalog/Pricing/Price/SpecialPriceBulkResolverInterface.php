<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Pricing\Price;

use Magento\Eav\Model\Entity\Collection\AbstractCollection;

interface SpecialPriceBulkResolverInterface
{
    public const DEFAULT_CACHE_LIFE_TIME = 31536000;

    /**
     * Generate special price flag for entire product listing
     *
     * @param int $storeId
     * @param AbstractCollection|null $productCollection
     * @return array
     */
    public function generateSpecialPriceMap(int $storeId, ?AbstractCollection $productCollection): array;
}
