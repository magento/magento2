<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */

namespace Magento\Customer\Block\Adminhtml\Form\Element;

/**
 * Customer Widget Form Boolean Element Block
 */
class Boolean extends \Magento\Framework\Data\Form\Element\Select
{
    /**
     * Prepare default SELECT values
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setValues([['label' => __('No'), 'value' => '0'], ['label' => __('Yes'), 'value' => 1]]);
    }
}
