<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Customer Widget Form Boolean Element Block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Customer\Block\Adminhtml\Form\Element;

class Boolean extends \Magento\Framework\Data\Form\Element\Select
{
    /**
     * Prepare default SELECT values
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setValues([['label' => __('No'), 'value' => '0'], ['label' => __('Yes'), 'value' => 1]]);
    }
}
