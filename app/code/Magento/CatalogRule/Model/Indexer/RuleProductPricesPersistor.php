<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Model\Indexer;

use Magento\CatalogRule\Model\Indexer\IndexerTableSwapperInterface as TableSwapper;
use Magento\Catalog\Model\ResourceModel\Indexer\ActiveTableSwitcher;
use Magento\Framework\App\ObjectManager;

/**
 * Persist product prices to index table.
 */
class RuleProductPricesPersistor
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resource;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    private $dateFormat;

    /**
<<<<<<< HEAD
     * @var TableSwapper
=======
     * @var ActiveTableSwitcher
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     */
    private $tableSwapper;

    /**
     * @var TableSwapper
     */
    private $tableSwapper;

    /**
     * @param \Magento\Framework\Stdlib\DateTime $dateFormat
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param ActiveTableSwitcher $activeTableSwitcher
     * @param TableSwapper|null $tableSwapper
<<<<<<< HEAD
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
=======
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     */
    public function __construct(
        \Magento\Framework\Stdlib\DateTime $dateFormat,
        \Magento\Framework\App\ResourceConnection $resource,
        ActiveTableSwitcher $activeTableSwitcher,
        TableSwapper $tableSwapper = null
    ) {
        $this->dateFormat = $dateFormat;
        $this->resource = $resource;
<<<<<<< HEAD
=======
        $this->activeTableSwitcher = $activeTableSwitcher;
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
        $this->tableSwapper = $tableSwapper ??
            ObjectManager::getInstance()->get(TableSwapper::class);
    }

    /**
     * Persist prices data to index table.
     *
     * @param array $priceData
     * @param bool $useAdditionalTable
     * @return bool
     * @throws \Exception
     */
    public function execute(array $priceData, $useAdditionalTable = false)
    {
        if (empty($priceData)) {
            return false;
        }

        $connection = $this->resource->getConnection();
        $indexTable = $this->resource->getTableName('catalogrule_product_price');
        if ($useAdditionalTable) {
            $indexTable = $this->resource->getTableName(
                $this->tableSwapper->getWorkingTableName('catalogrule_product_price')
            );
        }

        foreach ($priceData as $key => $data) {
            $priceData[$key]['rule_date'] = $this->dateFormat->formatDate($data['rule_date'], false);
            $priceData[$key]['latest_start_date'] = $this->dateFormat->formatDate(
                $data['latest_start_date'],
                false
            );
            $priceData[$key]['earliest_end_date'] = $this->dateFormat->formatDate(
                $data['earliest_end_date'],
                false
            );
        }
        $connection->insertOnDuplicate($indexTable, $priceData);

        return true;
    }
}
