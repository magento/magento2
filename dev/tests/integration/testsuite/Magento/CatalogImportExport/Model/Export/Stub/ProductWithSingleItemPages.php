<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogImportExport\Model\Export\Stub;

use Magento\CatalogImportExport\Model\Export\Product;

/**
 * Expose candidate loss at page boundaries without overriding the core loading hooks.
 */
class ProductWithSingleItemPages extends Product
{
    /**
     * Keep each matching product on its own logical page.
     *
     * @return int
     */
    protected function getItemsPerPage(): int
    {
        return 1;
    }
}
