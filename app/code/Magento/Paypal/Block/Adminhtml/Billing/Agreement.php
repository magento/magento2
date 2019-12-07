<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Block\Adminhtml\Billing;

/**
 * Adminhtml billing agreement grid container
 *
 * @api
 * @since 100.0.2
 */
class Agreement extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Initialize billing agreements grid container
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_billing_agreement';
        $this->_blockGroup = 'Magento_Paypal';
        $this->_headerText = __('Billing Agreements');
        parent::_construct();
        $this->buttonList->remove('add');
    }
}
