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

namespace Magento\Customer\Service\V1\Dto\Search;

use Magento\Customer\Service\V1\Dto\Filter;
use Magento\Service\Entity\AbstractDtoBuilder;

/**
 * Abstract Builder for AbstractFilterGroup DTOs.
 */
abstract class AbstractFilterGroupBuilder extends AbstractDtoBuilder
{
    /**
     * @param Filter $filter
     * @return $this
     */
    public function addFilter(Filter $filter)
    {
        if (!isset($this->_data[AbstractFilterGroup::FILTERS])
            || !is_array($this->_data[AbstractFilterGroup::FILTERS])
        ) {
            $this->_data[AbstractFilterGroup::FILTERS] = [];
        }
        $this->_data[AbstractFilterGroup::FILTERS][] = $filter;
        return $this;
    }

    /**
     * @param Filter[] $filters
     * @return $this
     */
    public function setFilters($filters)
    {
        return $this->_set(AbstractFilterGroup::FILTERS, $filters);
    }

    /**
     * @param FilterGroupInterface $group
     * @return $this
     */
    public function addGroup(FilterGroupInterface $group)
    {
        if (!isset($this->_data[AbstractFilterGroup::GROUPS])
            || !is_array($this->_data[AbstractFilterGroup::GROUPS])
        ) {
            $this->_data[AbstractFilterGroup::GROUPS] = [];
        }
        $this->_data[AbstractFilterGroup::GROUPS][] = $group;
        return $this;
    }

    /**
     * @param FilterGroupInterface[] $groups
     * @return $this
     */
    public function setGroups($groups)
    {
        return $this->_set(AbstractFilterGroup::GROUPS, $groups);
    }
}
