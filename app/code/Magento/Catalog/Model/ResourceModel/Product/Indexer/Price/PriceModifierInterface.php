<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\ResourceModel\Product\Indexer\Price;

/**
 * Interface for modifying price data in price index table.
 *
 * @api
 */
interface PriceModifierInterface
{
    /**
     * Modify price data.
     *
     * @param IndexTableStructure $priceTable
     * @param array $entityIds
     * @return void
     */
    public function modifyPrice(IndexTableStructure $priceTable, array $entityIds = []) : void;
}
