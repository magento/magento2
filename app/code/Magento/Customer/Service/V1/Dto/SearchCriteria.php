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
namespace Magento\Customer\Service\V1\Dto;

use Magento\Service\Entity\AbstractDto;

/**
 * DTO for SearchCriteria
 */
class SearchCriteria extends AbstractDto
{
    const SORT_ASC = 1;
    const SORT_DESC = -1;

    /**
     * @return \Magento\Customer\Service\V1\Dto\Search\FilterGroupInterface
     */
    public function getFilters()
    {
        return $this->_get('filters', $this->_createArray());
    }

    /**
     * @return array
     */
    public function getSortOrders()
    {
        return $this->_get('sort_orders', $this->_createArray());
    }

    /**
     * @return int
     */
    public function getPageSize()
    {
        return $this->_get('page_size');
    }

    /**
     * @return int
     */
    public function getCurrentPage()
    {
        return $this->_get('current_page');
    }

    /**
     * Create Array
     *
     * @todo to be implemented in MAGETWO-18201
     *
     * @return array
     */
    private function _createArray()
    {
    }
}
