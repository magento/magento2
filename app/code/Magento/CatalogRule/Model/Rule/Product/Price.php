<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Catalog Rule Product Aggregated Price per date Model
 *
 * @method \Magento\CatalogRule\Model\ResourceModel\Rule\Product\Price _getResource()
 * @method \Magento\CatalogRule\Model\ResourceModel\Rule\Product\Price getResource()
 * @method string getRuleDate()
 * @method \Magento\CatalogRule\Model\Rule\Product\Price setRuleDate(string $value)
 * @method int getCustomerGroupId()
 * @method \Magento\CatalogRule\Model\Rule\Product\Price setCustomerGroupId(int $value)
 * @method int getProductId()
 * @method \Magento\CatalogRule\Model\Rule\Product\Price setProductId(int $value)
 * @method float getRulePrice()
 * @method \Magento\CatalogRule\Model\Rule\Product\Price setRulePrice(float $value)
 * @method int getWebsiteId()
 * @method \Magento\CatalogRule\Model\Rule\Product\Price setWebsiteId(int $value)
 * @method string getLatestStartDate()
 * @method \Magento\CatalogRule\Model\Rule\Product\Price setLatestStartDate(string $value)
 * @method string getEarliestEndDate()
 * @method \Magento\CatalogRule\Model\Rule\Product\Price setEarliestEndDate(string $value)
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\CatalogRule\Model\Rule\Product;

use Magento\Framework\DB\Select;

class Price extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\CatalogRule\Model\ResourceModel\Rule\Product\Price');
    }

    /**
     * Apply price rule price to price index table
     *
     * @param Select $select
     * @param array|string $indexTable
     * @param string $entityId
     * @param string $customerGroupId
     * @param string $websiteId
     * @param array $updateFields       the array fields for compare with rule price and update
     * @param string $websiteDate
     * @return $this
     */
    public function applyPriceRuleToIndexTable(
        Select $select,
        $indexTable,
        $entityId,
        $customerGroupId,
        $websiteId,
        $updateFields,
        $websiteDate
    ) {
        $this->_getResource()->applyPriceRuleToIndexTable(
            $select,
            $indexTable,
            $entityId,
            $customerGroupId,
            $websiteId,
            $updateFields,
            $websiteDate
        );

        return $this;
    }
}
