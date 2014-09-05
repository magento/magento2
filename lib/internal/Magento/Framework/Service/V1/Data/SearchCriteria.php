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

namespace Magento\Framework\Service\V1\Data;

use Magento\Framework\Service\Data\AbstractExtensibleObject;

/**
 * Data Object for SearchCriteria
 */
class SearchCriteria extends AbstractExtensibleObject
{
    const SORT_ASC = 1;
    const SORT_DESC = -1;

    /**#@+
     * Constants for Data Object keys
     */
    const FILTER_GROUPS = 'filterGroups';
    const SORT_ORDERS = 'sort_orders';
    const PAGE_SIZE = 'page_size';
    const CURRENT_PAGE = 'current_page';

    /**
     * Returns a list of filter groups
     *
     * @return \Magento\Framework\Service\V1\Data\Search\FilterGroup[]
     */
    public function getFilterGroups()
    {
        return $this->_get(self::FILTER_GROUPS);
    }

    /**
     * Get sort order
     *
     * @return \Magento\Framework\Service\V1\Data\SortOrder[]|null
     */
    public function getSortOrders()
    {
        return $this->_get(self::SORT_ORDERS);
    }

    /**
     * Get page size
     *
     * @return int|null
     */
    public function getPageSize()
    {
        return $this->_get(self::PAGE_SIZE);
    }

    /**
     * Get current page
     *
     * @return int|null
     */
    public function getCurrentPage()
    {
        return $this->_get(self::CURRENT_PAGE);
    }
}
