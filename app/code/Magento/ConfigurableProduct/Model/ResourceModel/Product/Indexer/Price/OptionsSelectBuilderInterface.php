<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Model\ResourceModel\Product\Indexer\Price;

use Magento\Framework\DB\Select;

/**
 * Aggregate configurable product options prices and save it in a temporary index table
 */
interface OptionsSelectBuilderInterface
{
    /**
     * Return select with aggregated configurable product options prices
     *
     * @param string $indexTable
     * @param array|null $entityIds
     * @return Select
     */
    public function execute(string $indexTable, ?array $entityIds = null): Select;
}
