<?php
/************************************************************************
 *
 * Copyright 2023 Adobe
 * All Rights Reserved.
 *
 * NOTICE: All information contained herein is, and remains
 * the property of Adobe and its suppliers, if any. The intellectual
 * and technical concepts contained herein are proprietary to Adobe
 * and its suppliers and are protected by all applicable intellectual
 * property laws, including trade secret and copyright laws.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Adobe.
 * ************************************************************************
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
