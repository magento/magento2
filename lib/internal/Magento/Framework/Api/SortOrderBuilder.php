<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Framework\Api;


/**
 * Builder for sort order data object.
 *
 * @method SortOrder create()
 */
class SortOrderBuilder extends ExtensibleObjectBuilder
{
    /**
     * Set sorting field.
     *
     * @param string $field
     * @return $this
     */
    public function setField($field)
    {
        $this->_set(SortOrder::FIELD, $field);
        return $this;
    }

    /**
     * Set sorting direction.
     *
     * @param string $direction
     * @return $this
     */
    public function setDirection($direction)
    {
        $this->_set(SortOrder::DIRECTION, $direction);
        return $this;
    }
}
