<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Paypal\Block\Adminhtml\Billing\Agreement\View\Tab;

/**
 * Adminhtml billing agreement related orders tab
 */
class Orders extends \Magento\Framework\View\Element\Text\ListText implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * Initialize grid params
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('billing_agreement_orders');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Related Orders');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Related Orders');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }
}
