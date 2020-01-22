<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\ProductLink\Data;

/**
 * Criteria for finding lists.
 */
interface ListCriteriaInterface
{
    /**
     * Links belong to this product.
     *
     * @return string
     */
    public function getBelongsToProductSku(): string;

    /**
     * Limit links by type (in).
     *
     * @return string[]|null
     */
    public function getLinkTypes(): ?array;
}
