<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Indexer\Product\Flat;

/**
 * Interface TableDataInterface
 * @since 2.0.0
 */
interface TableDataInterface
{
    /**
     * Move data from temporary tables to flat
     *
     * @param string $flatTable
     * @param string $flatDropName
     * @param string $temporaryFlatTableName
     * @return void
     * @since 2.0.0
     */
    public function move($flatTable, $flatDropName, $temporaryFlatTableName);
}
