<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Catalog Rule Product Aggregated Price per date Resource Model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\CatalogRule\Model\ResourceModel\Rule\Product;

class Price extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize connection and define main table
     *
     * @return void
     * @codeCoverageIgnore
     */
    protected function _construct()
    {
        $this->_init('catalogrule_product_price', 'rule_product_price_id');
    }

    /**
     * Apply price rule price to price index table
     *
     * @param \Magento\Framework\DB\Select $select
     * @param array|string $indexTable
     * @param string $entityId
     * @param string $customerGroupId
     * @param string $websiteId
     * @param array $updateFields       the array of fields for compare with rule price and update
     * @param string $websiteDate
     * @return \Magento\CatalogRule\Model\ResourceModel\Rule\Product\Price
     */
    public function applyPriceRuleToIndexTable(
        \Magento\Framework\DB\Select $select,
        $indexTable,
        $entityId,
        $customerGroupId,
        $websiteId,
        $updateFields,
        $websiteDate
    ) {
        if (empty($updateFields)) {
            return $this;
        }

        if (is_array($indexTable)) {
            foreach ($indexTable as $k => $v) {
                if (is_string($k)) {
                    $indexAlias = $k;
                } else {
                    $indexAlias = $v;
                }
                break;
            }
        } else {
            $indexAlias = $indexTable;
        }

        $select->join(
            ['rp' => $this->getMainTable()],
            "rp.rule_date = {$websiteDate}",
            []
        )->where(
            "rp.product_id = {$entityId} AND rp.website_id = {$websiteId} AND rp.customer_group_id = {$customerGroupId}"
        );

        foreach ($updateFields as $priceField) {
            $priceCond = $this->getConnection()->quoteIdentifier([$indexAlias, $priceField]);
            $priceExpr = $this->getConnection()->getCheckSql(
                "rp.rule_price < {$priceCond}",
                'rp.rule_price',
                $priceCond
            );
            $select->columns([$priceField => $priceExpr]);
        }

        $query = $select->crossUpdateFromSelect($indexTable);
        $this->getConnection()->query($query);

        return $this;
    }
}
