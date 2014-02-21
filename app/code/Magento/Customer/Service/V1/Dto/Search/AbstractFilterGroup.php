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

use Magento\Service\Entity\AbstractDto;

/**
 * Groups two or more filters together using a logical group type
 */
abstract class AbstractFilterGroup extends AbstractDto implements FilterGroupInterface
{
    const FILTERS = 'filters';
    const GROUPS = 'groups';

    /**
     * {@inheritdoc}
     */
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->_data['group_type'] = $this->getGroupType();
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        $filters = $this->_get(self::FILTERS);
        return is_null($filters) ? [] : $filters;
    }

    /**
     * {@inheritdoc}
     */
    public function getGroups()
    {
        $groups = $this->_get(self::GROUPS);
        return is_null($groups) ? [] : $groups;
    }

    /**
     * {@inheritdoc}
     */
    public abstract function getGroupType();
}
