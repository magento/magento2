<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Layer attribute filter
 *
 */
namespace Magento\Catalog\Model\Layer\Search\Filter;

/**
 * Class \Magento\Catalog\Model\Layer\Search\Filter\Attribute
 *
 * @since 2.0.0
 */
class Attribute extends \Magento\Catalog\Model\Layer\Filter\Attribute
{
    /**
     * Check whether specified attribute can be used in LN
     *
     * @param \Magento\Catalog\Model\ResourceModel\Eav\Attribute  $attribute
     * @return int
     * @since 2.0.0
     */
    protected function getAttributeIsFilterable($attribute)
    {
        return $attribute->getIsFilterableInSearch();
    }
}
