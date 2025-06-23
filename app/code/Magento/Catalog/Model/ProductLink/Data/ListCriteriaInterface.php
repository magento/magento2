<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\ProductLink\Data;

/**
 * Criteria for finding lists.
 *
 * @api
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
