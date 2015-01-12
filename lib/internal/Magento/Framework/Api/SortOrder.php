<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Api;


/**
 * Data object for sort order.
 */
class SortOrder extends AbstractExtensibleObject
{
    const FIELD = 'field';
    const DIRECTION = 'direction';

    /**
     * Get sorting field.
     *
     * @return string
     */
    public function getField()
    {
        return $this->_get(SortOrder::FIELD);
    }

    /**
     * Get sorting direction.
     *
     * @return string
     */
    public function getDirection()
    {
        return $this->_get(SortOrder::DIRECTION);
    }
}
