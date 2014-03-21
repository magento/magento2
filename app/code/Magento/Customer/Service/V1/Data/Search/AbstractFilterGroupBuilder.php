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
namespace Magento\Customer\Service\V1\Data\Search;

use Magento\Service\Data\AbstractObjectBuilder;

/**
 * Abstract Builder for AbstractFilterGroup DATA.
 */
abstract class AbstractFilterGroupBuilder extends AbstractObjectBuilder
{
    /**
     * Add filter
     *
     * @param \Magento\Customer\Service\V1\Data\Filter $filter
     * @return $this
     */
    public function addFilter(\Magento\Customer\Service\V1\Data\Filter $filter)
    {
        if (!isset($this->_data[AbstractFilterGroup::FILTERS]) || !is_array($this->_data[AbstractFilterGroup::FILTERS])
        ) {
            $this->_data[AbstractFilterGroup::FILTERS] = array();
        }
        $this->_data[AbstractFilterGroup::FILTERS][] = $filter;
        return $this;
    }

    /**
     * Set filters
     *
     * @param \Magento\Customer\Service\V1\Data\Filter[] $filters
     * @return $this
     */
    public function setFilters($filters)
    {
        return $this->_set(AbstractFilterGroup::FILTERS, $filters);
    }

    /**
     * Add filter group
     *
     * @param \Magento\Customer\Service\V1\Data\Search\FilterGroupInterface $group
     * @return $this
     */
    public function addGroup(\Magento\Customer\Service\V1\Data\Search\FilterGroupInterface $group)
    {
        if (!isset($this->_data[AbstractFilterGroup::GROUPS]) || !is_array($this->_data[AbstractFilterGroup::GROUPS])
        ) {
            $this->_data[AbstractFilterGroup::GROUPS] = array();
        }
        $this->_data[AbstractFilterGroup::GROUPS][] = $group;
        return $this;
    }

    /**
     * Set filter groups
     *
     * @param \Magento\Customer\Service\V1\Data\Search\FilterGroupInterface[] $groups
     * @return $this
     */
    public function setGroups($groups)
    {
        return $this->_set(AbstractFilterGroup::GROUPS, $groups);
    }
}
