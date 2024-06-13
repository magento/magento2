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
class NewStatus extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'status';
        $this->_controller = 'adminhtml_order_status';
        $this->_blockGroup = 'Magento_Sales';
        $this->_mode = 'newStatus';

        parent::_construct();
        $this->buttonList->update('save', 'label', __('Save Status'));
        $this->buttonList->remove('delete');
    }

    /**
     * Retrieve text for header element depending on loaded page
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        return __('New Order Status');
    }
}
