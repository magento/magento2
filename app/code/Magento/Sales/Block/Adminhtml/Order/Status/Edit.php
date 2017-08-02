<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Block\Adminhtml\Order\Status;

/**
 * @api
 * @since 2.0.0
 */
class Edit extends \Magento\Sales\Block\Adminhtml\Order\Status\NewStatus
{
    /**
     * Constructor
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_mode = 'edit';
        $this->_blockGroup = 'Magento_Sales';
    }

    /**
     * Retrieve text for header element depending on loaded page
     *
     * @return \Magento\Framework\Phrase
     * @since 2.0.0
     */
    public function getHeaderText()
    {
        return __('Edit Order Status');
    }
}
