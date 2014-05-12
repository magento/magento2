<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Catalog Rule Product Aggregated Price per date Model
 *
 * @method \Magento\CatalogRule\Model\Resource\Rule\Product\Price _getResource()
 * @method \Magento\CatalogRule\Model\Resource\Rule\Product\Price getResource()
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
        $this->_init('Magento\CatalogRule\Model\Resource\Rule\Product\Price');
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
