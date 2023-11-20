<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
