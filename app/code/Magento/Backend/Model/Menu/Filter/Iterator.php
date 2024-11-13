<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Model\Menu\Filter;

/**
 * Menu filter iterator
 * @api
 * @since 100.0.2
 */
class Iterator extends \FilterIterator
{
    /**
     * Check whether the current element of the iterator is acceptable
     *
     * @return bool true if the current element is acceptable, otherwise false.
     */
    #[\ReturnTypeWillChange]
    public function accept()
    {
        return !($this->current()->isDisabled() || !$this->current()->isAllowed());
    }
}
