<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Model\Menu\Filter;

/**
 * Menu filter iterator
 * @api
 * @since 2.0.0
 */
class Iterator extends \FilterIterator
{
    /**
     * Constructor
     *
     * @param \Iterator $iterator
     * @since 2.0.0
     */
    public function __construct(\Iterator $iterator)
    {
        parent::__construct($iterator);
    }

    /**
     * Check whether the current element of the iterator is acceptable
     *
     * @return bool true if the current element is acceptable, otherwise false.
     * @since 2.0.0
     */
    public function accept()
    {
        return !($this->current()->isDisabled() || !$this->current()->isAllowed());
    }
}
