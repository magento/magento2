<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Adminhtml billing agreements tabs view
 */
namespace Magento\Paypal\Block\Adminhtml\Billing\Agreement\View;

/**
 * @api
 * @since 2.0.0
 */
class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * Initialize tab
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('billing_agreement_view_tabs');
        $this->setDestElementId('billing_agreement_view');
        $this->setTitle(__('Billing Agreement View'));
    }
}
