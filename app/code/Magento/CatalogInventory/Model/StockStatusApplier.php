<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogInventory\Model;

/**
 * Search Result Applier getters and setters
 */
class StockStatusApplier implements StockStatusApplierInterface
{
    /**
     * Storefront search result applier flag
     *
     * @var bool
     */
    private $searchResultApplier = false;

    /**
     * Set flag, if the request is originated from SearchResultApplier
     *
     * @param bool $status
     */
    public function setSearchResultApplier(bool $status): void
    {
        $this->searchResultApplier = $status;
    }

    /**
     * Get flag, if the request is originated from SearchResultApplier
     *
     * @return bool
     */
    public function hasSearchResultApplier() : bool
    {
        return $this->searchResultApplier;
    }
}
