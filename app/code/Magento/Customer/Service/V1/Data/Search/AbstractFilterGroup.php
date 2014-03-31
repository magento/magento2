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

use Magento\Service\Data\AbstractObject;

/**
 * Groups two or more filters together using a logical group type
 */
abstract class AbstractFilterGroup extends AbstractObject implements FilterGroupInterface
{
    const FILTERS = 'filters';

    const GROUPS = 'groups';

    /**
     * {@inheritdoc}
     */
    public function __construct(AbstractFilterGroupBuilder $builder)
    {
        parent::__construct($builder);
        $this->_data['group_type'] = $this->getGroupType();
    }

    /**
     * Returns a list of filters in this group
     *
     * @return \Magento\Customer\Service\V1\Data\Filter[]
     */
    public function getFilters()
    {
        $filters = $this->_get(self::FILTERS);
        return is_null($filters) ? array() : $filters;
    }

    /**
     * Returns a list of filter groups in this group
     *
     * @return \Magento\Customer\Service\V1\Data\Search\FilterGroupInterface[]
     */
    public function getGroups()
    {
        $groups = $this->_get(self::GROUPS);
        return is_null($groups) ? array() : $groups;
    }

    /**
     * Returns the grouping type such as 'OR' or 'AND'.
     *
     * @return string
     */
    abstract public function getGroupType();
}
