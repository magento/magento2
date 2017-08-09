<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Model\Indexer;

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
     * @var \Magento\Catalog\Model\ResourceModel\Indexer\ActiveTableSwitcher
     */
    private $activeTableSwitcher;

    /**
     * @param \Magento\Framework\Stdlib\DateTime $dateFormat
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Catalog\Model\ResourceModel\Indexer\ActiveTableSwitcher $activeTableSwitcher
     */
    public function __construct(
        \Magento\Framework\Stdlib\DateTime $dateFormat,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Catalog\Model\ResourceModel\Indexer\ActiveTableSwitcher $activeTableSwitcher
    ) {
        $this->dateFormat = $dateFormat;
        $this->resource = $resource;
        $this->activeTableSwitcher = $activeTableSwitcher;
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
                $this->activeTableSwitcher->getAdditionalTableName('catalogrule_product_price')
            );
        }

        $productIds = [];

        try {
            foreach ($priceData as $key => $data) {
                $productIds['product_id'] = $data['product_id'];
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
        } catch (\Exception $e) {
            throw $e;
        }
        return true;
    }
}
