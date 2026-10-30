<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Sales\Block\Adminhtml\Order\Status;

/**
 * @api
 * @since 100.0.2
 */
class Edit extends \Magento\Sales\Block\Adminhtml\Order\Status\NewStatus
{
    /**
     * Constructor
     *
     * @return void
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
     */
    public function getHeaderText()
    {
        return __('Edit Order Status');
    }
}
