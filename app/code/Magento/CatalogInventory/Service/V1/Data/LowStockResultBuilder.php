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
namespace Magento\CatalogInventory\Service\V1\Data;

/**
 * Low stock search result builder object
 *
 * @codeCoverageIgnore
 */
class LowStockResultBuilder extends \Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder
{
    /**
     * Set search criteria
     *
     * @param LowStockCriteria $searchCriteria
     * @return $this
     */
    public function setSearchCriteria(LowStockCriteria $searchCriteria)
    {
        return $this->_set(LowStockResult::SEARCH_CRITERIA, $searchCriteria);
    }

    /**
     * Set total count
     *
     * @param int $totalCount
     * @return $this
     */
    public function setTotalCount($totalCount)
    {
        return $this->_set(LowStockResult::TOTAL_COUNT, $totalCount);
    }

    /**
     * Set items
     *
     * @param array $items
     * @return $this
     */
    public function setItems($items)
    {
        return $this->_set(LowStockResult::PRODUCT_SKU_LIST, $items);
    }
}
