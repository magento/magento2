<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Indexer\Config\Converter;

/**
 * Interface for managing sort order of indexers
 *
 * Make sense for the full reindex when you need to adjust order od indexers execution
 */
interface SortingAdjustmentInterface
{
    /**
     * Make adjustments in the indexers list
     *
     * @param array $indexersList
     * @return array
     */
    public function adjust(array $indexersList) : array;
}
