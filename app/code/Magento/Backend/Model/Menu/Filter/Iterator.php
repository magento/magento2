<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Backend\Model\Menu\Filter;

/**
 * Menu filter iterator
 */
class Iterator extends \FilterIterator
{
    /**
     * Constructor
     *
     * @param \Iterator $iterator
     */
    public function __construct(\Iterator $iterator)
    {
        parent::__construct($iterator);
    }

    /**
     * Check whether the current element of the iterator is acceptable
     *
     * @return bool true if the current element is acceptable, otherwise false.
     */
    public function accept()
    {
        return !($this->current()->isDisabled() || !$this->current()->isAllowed());
    }
}
