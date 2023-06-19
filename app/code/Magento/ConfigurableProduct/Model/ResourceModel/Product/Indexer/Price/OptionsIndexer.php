<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Model\ResourceModel\Product\Indexer\Price;

use Magento\Catalog\Model\Indexer\Product\Price\TableMaintainer;

/**
 * Configurable product options prices aggregator
 */
class OptionsIndexer implements OptionsIndexerInterface
{
    /**
     * @var OptionsSelectBuilderInterface
     */
    private $selectBuilder;

    /**
     * @var TableMaintainer
     */
    private $tableMaintainer;

    /**
     * @param OptionsSelectBuilderInterface $selectBuilder
     * @param TableMaintainer $tableMaintainer
     */
    public function __construct(
        OptionsSelectBuilderInterface $selectBuilder,
        TableMaintainer $tableMaintainer
    ) {
        $this->selectBuilder = $selectBuilder;
        $this->tableMaintainer = $tableMaintainer;
    }

    /**
     * @inheritdoc
     */
    public function execute(string $indexTable, string $tempIndexTable, ?array $entityIds = null): void
    {
        $select = $this->selectBuilder->execute($indexTable, $entityIds);
        $this->tableMaintainer->insertFromSelect($select, $tempIndexTable, [
            "entity_id",
            "customer_group_id",
            "website_id",
            "min_price",
            "max_price",
            "tier_price",
        ]);
    }
}
