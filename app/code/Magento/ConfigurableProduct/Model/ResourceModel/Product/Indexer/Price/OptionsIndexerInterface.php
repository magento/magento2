<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Model\ResourceModel\Product\Indexer\Price;

use Magento\Framework\DB\Select;

/**
 * Configurable product options prices aggregator
 */
interface OptionsIndexerInterface
{
    /**
     * Aggregate configurable product options prices and save it in a temporary index table
     *
     * @param string $indexTable
     * @param string $tempIndexTable
     * @param array|null $entityIds
     */
    public function execute(string $indexTable, string $tempIndexTable, ?array $entityIds = null): void;
}
