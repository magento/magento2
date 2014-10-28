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
namespace Magento\Catalog\Service\V1\Data\Eav\AttributeSet;

/**
 * @codeCoverageIgnore
 */
class Attribute extends \Magento\Framework\Service\Data\AbstractExtensibleObject
{
    /**
     * table field for attribute_id
     */
    const ATTRIBUTE_ID = 'attribute_id';

    /**
     * table field for attribute_group_id
     */
    const ATTRIBUTE_GROUP_ID = 'attribute_group_id';

    /**
     * table field for sort order index
     */
    const SORT_ORDER = 'sort_order';

    /**
     * Get attribute id
     *
     * @return string
     */
    public function getAttributeId()
    {
        return $this->_get(self::ATTRIBUTE_ID);
    }

    /**
     * Get attribute id
     *
     * @return string
     */
    public function getAttributeGroupId()
    {
        return $this->_get(self::ATTRIBUTE_GROUP_ID);
    }

    /**
     * Get attribute set sort order index
     *
     * @return int
     */
    public function getSortOrder()
    {
        return $this->_get(self::SORT_ORDER);
    }
}
