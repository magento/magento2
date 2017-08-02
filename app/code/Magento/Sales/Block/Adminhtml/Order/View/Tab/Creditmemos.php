<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Block\Adminhtml\Order\View\Tab;

/**
 * Order Credit Memos grid
 *
 * @api
 * @since 2.0.0
 */
class Creditmemos extends \Magento\Framework\View\Element\Text\ListText implements
    \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getTabLabel()
    {
        return __('Credit Memos');
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getTabTitle()
    {
        return __('Order Credit Memos');
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function isHidden()
    {
        return false;
    }
}
