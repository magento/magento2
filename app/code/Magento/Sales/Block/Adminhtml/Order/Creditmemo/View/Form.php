<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Block\Adminhtml\Order\Creditmemo\View;

/**
 * Creditmemo view form
 *
 * @api
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Form extends \Magento\Sales\Block\Adminhtml\Order\AbstractOrder
{
    /**
     * Retrieve invoice order
     *
     * @return \Magento\Sales\Model\Order
     * @since 2.0.0
     */
    public function getOrder()
    {
        return $this->getCreditmemo()->getOrder();
    }

    /**
     * Retrieve source
     *
     * @return \Magento\Sales\Model\Order\Creditmemo
     * @since 2.0.0
     */
    public function getSource()
    {
        return $this->getCreditmemo();
    }

    /**
     * Retrieve order totals block settings
     *
     * @return array
     * @since 2.0.0
     */
    public function getOrderTotalData()
    {
        return ['grand_total_title' => __('Total Refund')];
    }

    /**
     * Retrieve creditmemo model instance
     *
     * @return \Magento\Sales\Model\Order\Creditmemo
     * @since 2.0.0
     */
    public function getCreditmemo()
    {
        return $this->_coreRegistry->registry('current_creditmemo');
    }

    /**
     * Get order url
     *
     * @return string
     * @since 2.0.0
     */
    public function getOrderUrl()
    {
        return $this->getUrl('sales/order/view', ['order_id' => $this->getCreditmemo()->getOrderId()]);
    }
}
