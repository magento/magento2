<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Customer Widget Form Boolean Element Block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Customer\Block\Adminhtml\Form\Element;

/**
 * Class \Magento\Customer\Block\Adminhtml\Form\Element\Boolean
 *
 * @since 2.0.0
 */
class Boolean extends \Magento\Framework\Data\Form\Element\Select
{
    /**
     * Prepare default SELECT values
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setValues([['label' => __('No'), 'value' => '0'], ['label' => __('Yes'), 'value' => 1]]);
    }
}
