<?php

namespace Magento\Catalog\Pricing\Price;

use Magento\Eav\Model\Entity\Collection\AbstractCollection;

interface SpecialPriceBulkResolverInterface
{
    public const DEFAULT_CACHE_LIFE_TIME = 31536000;

    /**
     * @param int $storeId
     * @param AbstractCollection|null $productCollection
     * @return array
     */
    public function generateSpecialPriceMap(int $storeId, ?AbstractCollection $productCollection): array;
}
