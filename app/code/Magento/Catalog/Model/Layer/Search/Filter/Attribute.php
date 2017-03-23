<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Layer attribute filter
 *
 */
namespace Magento\Catalog\Model\Layer\Search\Filter;

class Attribute extends \Magento\Catalog\Model\Layer\Filter\Attribute
{
    /**
     * Check whether specified attribute can be used in LN
     *
     * @param \Magento\Catalog\Model\ResourceModel\Eav\Attribute  $attribute
     * @return int
     */
    protected function getAttributeIsFilterable($attribute)
    {
        return $attribute->getIsFilterableInSearch();
    }
}
