<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\ResourceModel\Product\Indexer\Price;

/**
 * Interface for modifying price data in price index table.
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
<<<<<<< HEAD
    public function modifyPrice(IndexTableStructure $priceTable, array $entityIds = []);
=======
    public function modifyPrice(IndexTableStructure $priceTable, array $entityIds = []) : void;
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
}
