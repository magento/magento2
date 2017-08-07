<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Model\Indexer;

/**
 * Reindex information about rule relations with customer groups and websites.
 * @since 2.2.0
 */
class ReindexRuleGroupWebsite
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     * @since 2.2.0
     */
    private $dateTime;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     * @since 2.2.0
     */
    private $resource;

    /**
     * @var array
     * @since 2.2.0
     */
    private $catalogRuleGroupWebsiteColumnsList = ['rule_id', 'customer_group_id', 'website_id'];

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Indexer\ActiveTableSwitcher
     * @since 2.2.0
     */
    private $activeTableSwitcher;

    /**
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Catalog\Model\ResourceModel\Indexer\ActiveTableSwitcher $activeTableSwitcher
     * @since 2.2.0
     */
    public function __construct(
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Catalog\Model\ResourceModel\Indexer\ActiveTableSwitcher $activeTableSwitcher
    ) {
        $this->dateTime = $dateTime;
        $this->resource = $resource;
        $this->activeTableSwitcher = $activeTableSwitcher;
    }

    /**
     * Prepare and persist information about rule relations with customer groups and websites to index table.
     *
     * @param bool $useAdditionalTable
     * @return bool
     * @since 2.2.0
     */
    public function execute($useAdditionalTable = false)
    {
        $connection = $this->resource->getConnection();
        $timestamp = $this->dateTime->gmtTimestamp();

        $indexTable = $this->resource->getTableName('catalogrule_group_website');
        $ruleProductTable = $this->resource->getTableName('catalogrule_product');
        if ($useAdditionalTable) {
            $indexTable = $this->resource->getTableName(
                $this->activeTableSwitcher->getAdditionalTableName('catalogrule_group_website')
            );
            $ruleProductTable = $this->resource->getTableName(
                $this->activeTableSwitcher->getAdditionalTableName('catalogrule_product')
            );
        }

        $connection->delete($indexTable);
        $select = $connection->select()->distinct(
            true
        )->from(
            $ruleProductTable,
            $this->catalogRuleGroupWebsiteColumnsList
        )->where(
            "{$timestamp} >= from_time AND (({$timestamp} <= to_time AND to_time > 0) OR to_time = 0)"
        );
        $query = $select->insertFromSelect($indexTable, $this->catalogRuleGroupWebsiteColumnsList);
        $connection->query($query);
        return true;
    }
}
