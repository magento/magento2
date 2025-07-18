<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ImportExport\Model\Export;

use Magento\Framework\Data\Collection;
use Magento\ImportExport\Model\Export;

interface EntityFiltersProviderInterface
{
    /**
     * Get collection of filters for export entity
     *
     * @param Export $export
     * @return Collection
     */
    public function getFilters(Export $export): Collection;
}
