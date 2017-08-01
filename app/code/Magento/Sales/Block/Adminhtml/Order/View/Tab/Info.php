<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Block\Adminhtml\Order\View\Tab;

/**
 * Order information tab
 *
 * @api
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Info extends \Magento\Sales\Block\Adminhtml\Order\AbstractOrder implements
    \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * Retrieve order model instance
     *
     * @return \Magento\Sales\Model\Order
     * @since 2.0.0
     */
    public function getOrder()
    {
        return $this->_coreRegistry->registry('current_order');
    }

    /**
     * Retrieve source model instance
     *
     * @return \Magento\Sales\Model\Order
     * @since 2.0.0
     */
    public function getSource()
    {
        return $this->getOrder();
    }

    /**
     * Retrieve order totals block settings
     *
     * @return array
     * @since 2.0.0
     */
    public function getOrderTotalData()
    {
        return [
            'can_display_total_due' => true,
            'can_display_total_paid' => true,
            'can_display_total_refunded' => true
        ];
    }

    /**
     * Get order info data
     *
     * @return array
     * @since 2.0.0
     */
    public function getOrderInfoData()
    {
        return ['no_use_order_link' => true];
    }

    /**
     * Get tracking html
     *
     * @return string
     * @since 2.0.0
     */
    public function getTrackingHtml()
    {
        return $this->getChildHtml('order_tracking');
    }

    /**
     * Get items html
     *
     * @return string
     * @since 2.0.0
     */
    public function getItemsHtml()
    {
        return $this->getChildHtml('order_items');
    }

    /**
     * Retrieve gift options container block html
     *
     * @return string
     * @since 2.0.0
     */
    public function getGiftOptionsHtml()
    {
        return $this->getChildHtml('gift_options');
    }

    /**
     * Get payment html
     *
     * @return string
     * @since 2.0.0
     */
    public function getPaymentHtml()
    {
        return $this->getChildHtml('order_payment');
    }

    /**
     * View URL getter
     *
     * @param int $orderId
     * @return string
     * @since 2.0.0
     */
    public function getViewUrl($orderId)
    {
        return $this->getUrl('sales/*/*', ['order_id' => $orderId]);
    }

    /**
     * ######################## TAB settings #################################
     */

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getTabLabel()
    {
        return __('Information');
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getTabTitle()
    {
        return __('Order Information');
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
