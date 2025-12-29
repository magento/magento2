<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Indexer\Config\Converter;

class SortingAdjustment implements SortingAdjustmentInterface
{
    /**
     * @inheritDoc
     */
    public function adjust(array $indexersList): array
    {
        return $indexersList;
    }
}
