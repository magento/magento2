<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Model\ResourceModel\Product\Indexer\Price;

use Magento\Catalog\Model\Indexer\Product\Price\TableMaintainer;
use Magento\Framework\DB\Select;

/**
 * Populate index table with data from select
 */
class PopulateIndexTable implements PopulateIndexTableInterface
{
    /**
     * @var TableMaintainer
     */
    private $tableMaintainer;

    /**
     * @param TableMaintainer $tableMaintainer
     */
    public function __construct(
        TableMaintainer $tableMaintainer
    ) {
        $this->tableMaintainer = $tableMaintainer;
    }

    /**
     * @inheritdoc
     */
    public function execute(Select $select, string $indexTableName): void
    {
        $this->tableMaintainer->insertFromSelect($select, $indexTableName, []);
    }
}
